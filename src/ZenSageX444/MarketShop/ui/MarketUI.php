<?php

namespace ZenSageX444\MarketShop\ui;

use pocketmine\player\Player;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\item\LegacyStringToItemParser;

use ZenSageX444\MarketShop\Market;
use ZenSageX444\MarketShop\formapi\{
    ModalForm,
    SimpleForm,
    CustomForm
};
use ZenSageX444\MarketShop\database\Database;
use ZenSageX444\MarketShop\utils\EconomyUtils;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;

class MarketUI{
    
    private Market $plugin;
    
    public function __construct(Market $plugin){
        $this->plugin = $plugin;
    }
    
    public function getPlugin(): Market{
        return $this->plugin;
    }
    
    public function getDataBase(): DataBase{
        return $this->getPlugin()->getDataBase();
    }
    
    public function getPrefix(): string{
        return $this->getPlugin()->getPrefix();
    }
    
    public function MarketUI(Player $player, string $content = ""): void{
        $form = new SimpleForm(function(Player $player, $data){
            if($data === null)return;
            if($data == 0){
                $this->OpenShopMenu($player);
            }else if($data == 1){
                $this->AddShopUI($player);
            }else if($data == 2){
                $this->RemoveShopMenu($player);
            }
        });
        $form->setTitle("MarketShopUI");
        $form->setContent($content);
        $form->addButton("openMarketShop\nall item in market",0,"textures/ui/icon_blackfriday");
        $form->addButton("addShop\nadd item to market",0,"textures/ui/icon_best3");
        $form->addButton("removeShop\ncancel item in market",0,"textures/ui/icon_none");
        $form->sendToPlayer($player);
    }
    
    private array $time = array();
    
    public function AddShopUI(Player $player, string $content = ""): void{
        $item = $player->getInventory()->getItemInHand();
        if($item->isNull()){
            $this->MarketUI($player, "\n §cno item in hand\n\n");
            return;
        }
        
        $name = $player->getName();
        if(isset($this->time[$name])){
            if($this->time[$name] >= time()){
                $time = $this->time[$name] - time();
                $time = gmdate("i:s",$time);
                $this->MarketUI($player, "\n*** §cCooldown $time §f***\n\n");
                return;
            }
        }
        
        
        $form = new CustomForm(function(Player $player, $data)use($item, $name){
            if($data === null)return;
            if(!is_numeric($data[1])){
                $this->AddShopUI($player, "\n §cnumber only§f\n\n");
                return;
            }
            if($data[1] <= 0){
                $this->AddShopUI($player, "\n §cnumber only§f\n\n");
                return;
            }
            $amount = $item->getCount() - $data[2];
            $item->setCount($data[2]);
            $this->getDataBase()->addShop($player->getName(), $data[1], $item);
            $item->setCount($amount);
            $player->getInventory()->setItemInHand($item);
            $player->sendMessage($this->getPrefix()." Add Item to MarketShop Successful");
            
            $time = $this->getPlugin()->getConfig()->get("cooldown");
            if(!is_int($time)){
                $time = 120;
            }
            $this->time[$name] = time() + $time;
        });
        $form->setTitle("addShopUI");
        $form->addLabel($content."§eItem in hand: §f{$item->getName()} §ax§f{$item->getCount()}");
        $form->addInput("price","price","100");
        $form->addSlider("amount", 1, $item->getCount());
        $form->sendToPlayer($player);
    }
    
    public function OpenShopMenu(Player $player, int $page = 0): void{
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->setName($this->getPrefix()." §dMarketShopGUI");
        $inv = $menu->getInventory();
        $next = LegacyStringToItemParser::getInstance()->parse('351:10');
        $next->getNamedTag()->setInt("page", $page);
        $next->setCustomName("§ePage:§f ".($page+1)."/".count($this->getDataBase()->makeMarketMenuPage())."\n§bClick to next");
        
        $back = LegacyStringToItemParser::getInstance()->parse('351:5');
        $back->getNamedTag()->setInt("page", $page);
        $back->setCustomName("§ePage:§f ".($page+1)."/".count($this->getDataBase()->makeMarketMenuPage())."\n§bClick to back");
        $inv->setItem(50, $next);
        $inv->setItem(48, $back);
        if(isset($this->getDataBase()->makeMarketMenuPage()[$page])){
            foreach($this->getDataBase()->makeMarketMenuPage()[$page] as $item){
                $tag = $item->getNamedTag();
                $id = $tag->getInt("id");
                $price = $tag->getInt("price");
                $owner = $tag->getString("owner");
                $tag->setString("market", "market");
                $item->setLore(["§amarketId:§f $id","§aprice:§f $price §e$","§aseller:§f $owner"]);
                $inv->addItem($item);
            }
        }
        $menu->setListener(function(InvMenuTransaction $transaction): InvMenuTransactionResult{
            $player = $transaction->getPlayer();
            $itemClicked = $transaction->getItemClicked();
            $itemClickedWith = $transaction->getItemClickedWith();
            $action = $transaction->getAction();
            $invTransaction = $transaction->getTransaction();
            
            $inv = $action->getInventory();
            $tag = $itemClicked->getNamedTag();
            if($tag->getTag("market") != null){
                $player->removeCurrentWindow();
                $this->ConfirmBuyShopUI($player, $tag);
            }else if($tag->getTag("page") != null){
                $page = $tag->getInt("page") + 1;
                if($action->getSlot() == 48){
                    $page = $tag->getInt("page") - 1;
                }
                if(!isset($this->getDataBase()->makeMarketMenuPage()[$page])){
                    return $transaction->discard();
                }
                $inv->clearAll();
                $next = LegacyStringToItemParser::getInstance()->parse('351:10');
                $next->getNamedTag()->setInt("page", $page);
                $next->setCustomName("§ePage:§f ".($page+1)."/".count($this->getDataBase()->makeMarketMenuPage())."\n§bClick to next");
                
                $back = LegacyStringToItemParser::getInstance()->parse('351:5');
                $back->getNamedTag()->setInt("page", $page);
                $back->setCustomName("§ePage:§f ".($page+1)."/".count($this->getDataBase()->makeMarketMenuPage())."\n§bClick to back");
                $inv->setItem(50, $next);
                $inv->setItem(48, $back);
                foreach($this->getDataBase()->makeMarketMenuPage()[$page] as $item){
                    $tag = $item->getNamedTag();
                    $id = $tag->getInt("id");
                    $price = $tag->getInt("price");
                    $owner = $tag->getString("owner");
                    $tag->setString("market", "market");
                    $item->setLore(["§amarketId:§f $id","§aprice:§f $price §e$","§aseller:§f $owner"]);$inv->addItem($item);
                }
            }
            return $transaction->discard();
        });
        $menu->send($player);
    }
    
	public function ConfirmBuyShopUI(Player $player, CompoundTag $tag): void{
	    $id = $tag->getInt("id");
	    $price = $tag->getInt("price");
	    $owner = $tag->getString("owner");
	    $item = $this->getDataBase()->getShopItem($id);
	    if($item == null){
	        $player->sendMessage($this->getPrefix()." §cThe product has been sold or has been discontinued.");
	        return;
	    }
	    $form = new ModalForm(function (Player $player, $data)use($id, $price, $owner, $item){
	        if($data === null or $data == 0)return;
	        if(!$this->getDataBase()->isShop($id)){
	            $player->sendMessage($this->getPrefix()." §cThe product has been sold or has been discontinued.");
	        }
	        
	        $bedrockEconomy = new EconomyUtils();
	        $bedrockEconomy->takeEconomyMoney($player->getName(),$price)->onCompletion(function (bool $updated) use ($player, $id, $price, $owner, $item, $bedrockEconomy): void{
	            if(!$updated){
	                $player->sendMessage($this->getPrefix()." §cYou do not have enough money!!");
	                return;
	            }
	            if(!$player->getInventory()->canAddItem($item)){
	                $player->sendMessage($this->getPrefix()." §cInventoryMax");
	                return;
	            }
	            $player->getInventory()->addItem($item);
	            $this->getDataBase()->removeShop($id);
	            $player->sendMessage($this->getPrefix()." §aAlready purchased §eprice§f $price §e$");
	            $name = $player->getName();
	            $target = $this->getPlugin()->getServer()->getPlayerExact($owner);
	            if($target instanceof Player){
	                $target->sendMessage($this->getPrefix()." $name §ahas purchased your item for a §eprice§f $price §e$");
	            }
	            $bedrockEconomy->addEconomyMoney($owner,$price)->onCompletion(function (bool $updated) use ($player): void{
	                if(!$updated) return;
	                    
	            }, static fn() => null);
	        }, static fn() => null);
	    });
	    $form->setTitle("Confirm Purchase");
	    $form->setContent("§eItem:§f {$item->getName()} x{$item->getCount()}\n§eprice:§f $price §e$\n§emarketId:§f $id\n§eseller:§f $owner");
	    $form->setButton1("§f[§aConfirm§f]");
	    $form->setButton2("§f[§cCancel§f]");
	    $form->sendToPlayer($player);
	}	    
    
    public function RemoveShopMenu(Player $player, int $page = 0): void{
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->setName($this->getPrefix()." §cCancel your item MarketShop");
        $inv = $menu->getInventory();
        $next = LegacyStringToItemParser::getInstance()->parse('351:10');
        $next->getNamedTag()->setInt("page", $page);
        $next->setCustomName("§ePage:§f ".($page+1)."/".count($this->getDataBase()->makeMarketMenuPage())."\n§bClick to next");
        
        $back = LegacyStringToItemParser::getInstance()->parse('351:5');
        $back->getNamedTag()->setInt("page", $page);
        $back->setCustomName("§ePage:§f ".($page+1)."/".count($this->getDataBase()->makeMarketMenuPage())."\n§bClick to back");
        $inv->setItem(50, $next);
        $inv->setItem(48, $back);
        if(isset($this->getDataBase()->makeMarketMenuPageOwner($player->getName())[$page])){
            foreach($this->getDataBase()->makeMarketMenuPageOwner($player->getName())[$page] as $item){
                $tag = $item->getNamedTag();
                $id = $tag->getInt("id");
                $price = $tag->getInt("price");
                $owner = $tag->getString("owner");
                $tag->setString("market", "market");
                $item->setLore(["§amarketId:§f $id","§aprice:§f $price §e$","§aseller:§f $owner","§c**click to cancel item**"]);
                $inv->addItem($item);
            }
        }
        $menu->setListener(function(InvMenuTransaction $transaction): InvMenuTransactionResult{
            $player = $transaction->getPlayer();
            $itemClicked = $transaction->getItemClicked();
            $itemClickedWith = $transaction->getItemClickedWith();
            $action = $transaction->getAction();
            $invTransaction = $transaction->getTransaction();
            
            $inv = $action->getInventory();
            $tag = $itemClicked->getNamedTag();
            if($tag->getTag("market") != null){
                $id = $tag->getInt("id");
                $item = $this->getDataBase()->getShopItem($id);
                if($item != null){
                    if(!$player->getInventory()->canAddItem($item)){
                        $player->removeCurrentWindow();
                        $player->sendMessage($this->getPrefix()." §cInventoryMax");
                        return $transaction->discard();
                    }
                    $this->getDataBase()->removeShop($id);
                    $itemClicked->setCount(0);
                    $inv->setItem($action->getSlot(), $itemClicked);
                    $player->getInventory()->addItem($item);
                    $player->sendMessage($this->getPrefix()." §dCancel MarketShop§f $id §dSuccessful");
                }else{
                    $player->removeCurrentWindow();
                    $player->sendMessage($this->getPrefix()." §cSomeone has already bought it");
                    return $transaction->discard();
                }
            }else if($tag->getTag("page") != null){
                $page = $tag->getInt("page") + 1;
                if($action->getSlot() == 48){
                    $page = $tag->getInt("page") - 1;
                }
                if(!isset($this->getDataBase()->makeMarketMenuPageOwner($player->getName())[$page])){
                    return $transaction->discard();
                }
                $inv->clearAll();
                $next = LegacyStringToItemParser::getInstance()->parse('351:10');
                $next->getNamedTag()->setInt("page", $page);
                $next->setCustomName("§ePage:§f ".($page+1)."/".count($this->getDataBase()->makeMarketMenuPage())."\n§bClick to next");
                
                $back = LegacyStringToItemParser::getInstance()->parse('351:5');
                $back->getNamedTag()->setInt("page", $page);
                $back->setCustomName("§ePage:§f ".($page+1)."/".count($this->getDataBase()->makeMarketMenuPage())."\n§bClick to back");
                $inv->setItem(50, $next);
                $inv->setItem(48, $back);
                foreach($this->getDataBase()->makeMarketMenuPageOwner($player->getName())[$page] as $item){
                    $tag = $item->getNamedTag();
                    $id = $tag->getInt("id");
                    $price = $tag->getInt("price");
                    $owner = $tag->getString("owner");
                    $tag->setString("market", "market");
                    $item->setLore(["§amarketId:§f $id","§aprice:§f $price §e$","§aseller:§f $owner","§c**click to cancel item**"]);
                    $inv->addItem($item);
                }
            }
            return $transaction->discard();
        });
        $menu->send($player);
    }	
}
