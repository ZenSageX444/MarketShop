<?php

namespace ZenSageX444\MarketShop;

use pocketmine\plugin\PluginBase;

use muqsit\invmenu\{
    InvMenu,
    InvMenuHandler
};
use ZenSageX444\MarketShop\database\{
    YML,
    Database
};
use ZenSageX444\MarketShop\ui\MarketUI;

class Market extends PluginBase{
    
    private string $prefix = "???";
    private Database $dataBase;
    private MarketUI $marketUI;
    
    public function onLoad(): void{
        $this->getLogger()->info(" plugin loading");
    }
    
    public function onEnable(): void{
        $this->saveDefaultConfig();
        $this->prefix = $this->getConfig()->get("prefix");
        switch($this->getConfig()->get("databaseType")){
            case "yml":
               $this->dataBase = new YML($this, "Yaml");
               break;
            default:
               $this->dataBase = new YML($this, "Yaml");
               break;
         }
         
         if(!class_exists(InvMenu::class)){
             $this->getLogger()->info($this->getPrefix()." §eInvMenu §cnot found Please Install");
             $this->getServer()->getPluginManager()->disablePlugin($this);
             return;
         }else{
             if(!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
         }
         $this->marketUI = new MarketUI($this);
         $this->getServer()->getCommandMap()->register("market", new cmd\MarketCmd($this));
        
        $this->getLogger()->info($this->getPrefix()." §ai've been enabled...");
    }
    
    public function onDisable(): void{
        $this->getLogger()->info($this->getPrefix()." §ci've been disable...");
    }
    
    public function getPrefix(): string{
        return $this->prefix;
    }
    
    public function getDataBase(): Database{
        return $this->dataBase;
    }
    
    public function getMarketUI(): MarketUI{
        return $this->marketUI;
    }

}
