<?php

namespace ZenSageX444\MarketShop\database;

use ZenSageX444\MarketShop\Market;

interface Database{
    
    public function __construct(Market $plugin, string $type);
    
    public function getType(): string;
    
}
