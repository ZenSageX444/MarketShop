<?php

namespace ZenSageX444\MarketShop\cmd;

use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\permission\DefaultPermissionNames;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

use ZenSageX444\MarketShop\Market;

class MarketCmd extends Command{
    
    private Market $plugin;
    
    public function __construct(Market $plugin){
        $this->plugin = $plugin;
        parent::__construct("market");
        DefaultPermissions::registerPermission(
            new Permission("market.cmd"),
            [PermissionManager::getInstance()->getPermission(DefaultPermissionNames::GROUP_USER)]
        );
        $this->setPermission("market.cmd");
    }
    
    public function getPlugin(): Market{
      return $this->plugin;
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if($sender instanceof Player){
            $player = $sender;
            $this->getPlugin()->getMarketUI()->MarketUI($player);
        }else{
            $sender->sendMessage($this->getPlugin()->getPrefix()." §cCommand only in game");
        }
        return true;
    }
}
