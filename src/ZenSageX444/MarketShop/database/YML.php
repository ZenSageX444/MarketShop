<?php

namespace ZenSageX444\MarketShop\database;

use pocketmine\item\Item;
use pocketmine\utils\Config;

use ZenSageX444\MarketShop\Market;
use ZenSageX444\MarketShop\utils\MarketUtils;

class YML implements Database{
    
    private Market $plugin;
    private string $type;
    private Config $data;
    
    public function __construct(Market $plugin, string $type){
        $this->plugin = $plugin;
        $this->type = $type;
        $this->data = new Config($plugin->getDataFolder()."MarketShop.yml", Config::YAML, array());
    }
    
    public function getPlugin(): Market{
        return $this->plugin;
    }
    
    public function getType(): string{
        return $this->type;
    }
    
    public function getData(): Config{
        return $this->data;
    }
    
    public function addShop(string $owner, int $price, Item $item): void{
        $data = $this->getData();
        $dataAll = $data->getAll();
        $id = count($dataAll);
        if($id > 0){
            $id = end($dataAll)["id"] + 1;
        }
        
        $tag = $item->getNamedTag();
        $tag->setInt("id", $id);
        $tag->setInt("price", $price);
        $tag->setString("owner", $owner);
        
        $dataAll[$id] = [
            "id" => $id,
            "item" => MarketUtils::serializeItem($item)
        ];
        $data->setAll($dataAll);
        $data->save();
    }
    
    public function getShopItem(int $id): ?Item{
        $data = $this->getData();
        $dataAll = $data->getAll();
        $item = null;
        if($this->isShop($id)){
            $item = MarketUtils::deserializeItem($dataAll[$id]["item"]);
        }
        return $item;
    }
    
    public function isShop(int $id): bool{
        $data = $this->getData();
        $dataAll = $data->getAll();
        return isset($dataAll[$id]);
    }
    
    public function getShopIds(): array{
        $data = $this->getData();
        $dataAll = $data->getAll();
        return array_keys($dataAll);
    }
    
    public function removeShop(int $id): void{
        $data = $this->getData();
        $dataAll = $data->getAll();
        if($this->isShop($id)){
            unset($dataAll[$id]);
            $data->setAll($dataAll);
            $data->save();
        }
    }
    
    public function makeMarketMenuPage(): array{
        $items = [];
        foreach($this->getShopIds() as $id){
            $items[] = $this->getShopItem($id);
        }
        return array_chunk($items, 45);
    }
    
    public function makeMarketMenuPageOwner(string $owner): array{
        $items = [];
        foreach($this->getShopIds() as $id){
            $item = $this->getShopItem($id);
            if($item->getNamedTag()->getString("owner") == $owner){
                $items[] = $item;
            }
        }
        return array_chunk($items, 45);
    }
    
}