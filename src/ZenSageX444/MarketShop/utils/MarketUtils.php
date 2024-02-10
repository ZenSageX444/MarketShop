<?php 

namespace ZenSageX444\MarketShop\utils;

use pocketmine\item\Item;
use pocketmine\nbt\{
    TreeRoot,
    BigEndianNbtSerializer
};

class MarketUtils{
    
    public static function ItemSerialize(Item $item) : string{
        $result = zlib_encode((new BigEndianNbtSerializer())->write(new TreeRoot($item->nbtSerialize())), ZLIB_ENCODING_GZIP);
        if($result === false){
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new RuntimeException("Failed to serialize item " . json_encode($item, JSON_THROW_ON_ERROR));
        }

        return $result;
    }

    public static function ItemDeserialize(string $string) : Item{
        return Item::nbtDeserialize((new BigEndianNbtSerializer())->read(zlib_decode(hex2bin($string)))->mustGetCompoundTag());
    }
    
    public static function serializeItem(Item $item): string{
        return base64_encode((new BigEndianNbtSerializer())->write(new TreeRoot($item->nbtSerialize())));
    }

    public static function deserializeItem(string $data): Item{
        $data = base64_decode($data);
        return Item::nbtDeserialize((new BigEndianNbtSerializer())->read($data)->mustGetCompoundTag());
    }
    
}
