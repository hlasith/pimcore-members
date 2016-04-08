<?php

namespace Members\Model;

use Pimcore\Tool;
use Pimcore\Model;

class Configuration extends Model\AbstractModel
{

    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $data;

    /**
     * @var integer
     */
    public $creationDate;

    /**
     * @var integer
     */
    public $modificationDate;

    /**
     * this is a small per request cache to know which configuration is which is, this info is used in self::getByKey()
     *
     * @var array
     */
    protected static $nameIdMappingCache = array();

    /**
     * @param integer $id
     * @return Configuration
     */
    public static function getById($id)
    {
        $cacheKey = 'members_configuration_' . $id;

        try
        {
            $configurationEntry = \Zend_Registry::get($cacheKey);
            if (!$configurationEntry) {
                throw new \Exception('Configuration in registry is null');
            }
        }
        catch (\Exception $e)
        {
            try
            {
                $configurationEntry = new self();
                \Zend_Registry::set($cacheKey, $configurationEntry);
                $configurationEntry->setId(intval($id));
                $configurationEntry->getDao()->getById();
            }
            catch (\Exception $e)
            {
                \Logger::error($e);
                return null;
            }
        }

        return $configurationEntry;
    }

    /**
     * @param string $key
     * @param boolean $returnObject
     * @return mixed|null
     */
    public static function get($key, $returnObject = false)
    {
        $cacheKey = $key . '~~~';

        // check if pimcore already knows the id for this $name, if yes just return it
        if (array_key_exists($cacheKey, self::$nameIdMappingCache))
        {
            $entry = self::getById(self::$nameIdMappingCache[$cacheKey]);

            if ($returnObject) {
                return $entry;
            }

            return $entry instanceof Configuration ? $entry->getData() : null;
        }

        // create a tmp object to obtain the id
        $configurationEntry = new self();

        try
        {
            $configurationEntry->getDao()->getByKey($key);
        }
        catch (\Exception $e)
        {
            \Logger::warn($e);
            return null;
        }

        // to have a singleton in a way. like all instances of Element\ElementInterface do also, like Object\AbstractObject
        if ($configurationEntry->getId() > 0)
        {
            // add it to the mini-per request cache
            self::$nameIdMappingCache[$cacheKey] = $configurationEntry->getId();
            $entry = self::getById($configurationEntry->getId());

            if ($returnObject)
            {
                return $entry;
            }

            return $entry instanceof Configuration ? $entry->getData() : null;
        }
    }

    /**
     * set data for key
     *
     * @param $key
     * @param $data
     */
    public static function set($key, $data)
    {
        $configEntry = self::get($key, true);

        if (!$configEntry)
        {
            $configEntry = new self();
            $configEntry->setKey($key);
        }

        $configEntry->setData($data);
        $configEntry->save();
    }

    /**
     * @return mixed|null|\Zend_Config_Xml
     * @throws \Zend_Exception
     */
    public static function getPluginConfig()
    {
        $config = null;

        if (\Zend_Registry::isRegistered('members_plugin_config'))
        {
            $config = \Zend_Registry::get('members_plugin_config');
        }
        else
        {
            try
            {
                $config = new \Zend_Config_Xml(MEMBERS_PLUGIN_CONFIG);
                self::setPluginConfig($config);
            }
            catch (\Exception $e)
            {
                if (is_file(MEMBERS_PLUGIN_CONFIG))
                {
                    $m = 'Your plugin_xml.xml located at ' . MEMBERS_PLUGIN_CONFIG. ' is invalid, please check and correct it manually!';
                    Tool::exitWithError($m);
                }
            }
        }

        return $config;
    }

    /**
     * @static
     * @param \Zend_Config $config
     * @return void
     */
    public static function setPluginConfig(\Zend_Config $config)
    {
        \Zend_Registry::set('members_plugin_config', $config);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param int $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return int
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param int $modificationDate
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }
}
