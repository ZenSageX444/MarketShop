<?php

namespace ZenSageX444\MarketShop\utils;

use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;

class EconomyUtils{
    
    public function addEconomyMoney(string $player, $amount)
    {
        $promise = new PromiseResolver();
        BedrockEconomyAPI::beta()->add($player, $amount)->onCompletion(function () use ($promise): void {
            $promise->resolve(true);
        }, function () use ($promise): void {
            $promise->resolve(false);
        });
        return $promise->getPromise();
    }

    public function takeEconomyMoney(string $player, $amount) : Promise
    {
        $promise = new PromiseResolver();
        BedrockEconomyAPI::beta()->deduct($player, $amount)->onCompletion(function () use ($promise): void {
            $promise->resolve(true);
        }, function () use ($promise): void {
            $promise->resolve(false);
        });
        return $promise->getPromise();
    }

    public function getEconomyMoney(string $player) : Promise
    {
        $promise = new PromiseResolver();
        BedrockEconomyAPI::beta()->get($player)->onCompletion(function (float $balance) use ($promise): void {
            $promise->resolve($balance);
        }, function (float $balance) use ($promise): void {
            $promise->resolve($balance);
        });
        return $promise->getPromise();
    }
    
}