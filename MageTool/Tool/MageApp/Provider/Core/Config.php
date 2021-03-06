<?php

/**
 * @see MageTool_Tool_Core_Provider_Abstract
 */
require_once 'MageTool/Tool/MageApp/Provider/Abstract.php';
require_once 'Zend/Tool/Framework/Provider/Pretendable.php';

/**
 * MageTool_Tool_MageApp_Provider_Core_Config provides commands to read and update the Magento
 * config from the cli
 *
 * @package MageTool_MageApp_Providor_Core
 * @author Alistair Stead
 **/
class MageTool_Tool_MageApp_Provider_Core_Config extends MageTool_Tool_MageApp_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Pretendable
{
    /**
     * Define the name of the provider
     *
     * @return string
     * @author Alistair Stead
     **/
    public function getName()
    {
        return 'MageCoreConfig';
    }
    
    /**
     * Retrive a list of installed resources
     *
     * @return void
     * @author Alistair Stead
     **/
    public function show($path = null, $scope = null)
    {
        $this->_bootstrap();
        
        $this->_response->appendContent(
            'Magento Config Data: $PATH [$SCOPE] = $VALUE',
            array('color' => array('yellow'))
        );
            
        $configCollection = $configs = Mage::getModel('core/config_data')->getCollection();

        if (is_string($path)) {
            $configCollection->addFieldToFilter('path', array("like" => "%$path%"));
        }
        if (is_string($scope)) {
            $configCollection->addFieldToFilter('scope', array("eq" => $scope));
        }
        $configCollection->load();

        foreach ($configs as $key => $config) {
            $this->_response->appendContent(
                "{$config->getPath()} [{$config->getScope()}] = {$config->getValue()}",
                array('color' => array('white'))
            );
        }
    }
    
    /**
     * Set the value of a config value that matches a path and scope.
     *
     * @return void
     * @author Alistair Stead
     **/
    public function set($path, $value, $scope = null)
    {
        $this->_bootstrap();
        
        $this->_response->appendContent(
            'Magento Config updated to: $PATH [$SCOPE] = $VALUE',
            array('color' => array('yellow'))
        );
            
        $configCollection = Mage::getModel('core/config_data')->getCollection();
            
        $configCollection->addFieldToFilter('path', array("eq" => $path));
        if (is_string($scope)) {
            $configCollection->addFieldToFilter('scope', array("eq" => $scope));
        }
        $configCollection->load();
            
        foreach ($configCollection as $key => $config) {
            $config->setValue($value);
            if ($this->_registry->getRequest()->isPretend()) {
                $result = "Dry run";
            } else {
                $result = "Saved";
                $config->save();
            }  

            $this->_response->appendContent(
                "{$result} > {$config->getPath()} [{$config->getScope()}] = {$config->getValue()}",
                array('color' => array('white'))
            );
        }
    }
    
    /**
     * Update a config value that matches a path and scope by using str_replace
     *
     * @return void
     * @author Alistair Stead
     **/
    public function replace($match, $value, $path = null, $scope = null)
    {
        $this->_bootstrap();
        
        $this->_response->appendContent(
            'Magento Config updated to: $PATH [$SCOPE] = $VALUE',
            array('color' => array('yellow'))
        );
            
        $configCollection = $configs = Mage::getModel('core/config_data')->getCollection();

        if (is_string($path)) {
            $configCollection->addFieldToFilter('path', array("eq" => $path));
        }
        if (is_string($scope)) {
            $configCollection->addFieldToFilter('scope', array("eq" => $scope));
        }
        $configCollection->load();

        foreach ($configs as $key => $config) {
            if (strstr($config->getvalue(), $match)) {
                $config->setValue(str_replace($match, $value, $config->getvalue()));
                
                if ($this->_registry->getRequest()->isPretend()) {
                    $result = "Dry run";
                } else {
                    $result = "Saved";
                    $config->save();
                }  

                $this->_response->appendContent(
                    "{$result} > {$config->getPath()} [{$config->getScope()}] = {$config->getValue()}",
                    array('color' => array('white'))
                );
            }
        }
    }
}