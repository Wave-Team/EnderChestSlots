<?php

namespace Seyz\EnderChestSlots;

use pocketmine\{
    plugin\PluginBase,
    event\Listener,
    event\inventory\InventoryOpenEvent,
    event\inventory\InventoryTransactionEvent,
    inventory\EnderChestInventory,
    item\Item,
    nbt\tag\CompoundTag,
    nbt\tag\StringTag,
    Player,
    utils\Config
};

class Main extends PluginBase implements Listener
{
    
    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
    public function onOpenEnderchest(InventoryOpenEvent $e): void
    {
        $inv = $e->getInventory();
        $p = $e->getPlayer();
        
        if ($inv instanceof EnderChestInventory){
            $config = $this->getConfig();
            $perms = $config->getNested("permissions");
            $filtered = [];
            
            foreach($perms as $perm){
                $permName = $perm["permission"];
                
                if($permName == "default" || $p->hasPermission($permName)){
                    $filtered[] = $perm;
                }
            }
            
            $perms = $filtered;
            
            usort($perms, function($one, $two){
                return $two["slots"] - $one["slots"];
            });
            
            if(isset($perms[0])){
                $this->setSlots($p, $perms[0]["slots"]);
            }
        }
    }
    
    public function onEnderchestTransaction(InventoryTransactionEvent $e): void
    {
        $transactions = $e->getTransaction()->getActions();
        
        foreach ($transactions as $transaction){
            $item =$transaction->getSourceItem();
            $nbt = ($item->getNamedTag() ?? new CompoundTag());
            $item1 =$transaction->getTargetItem();
            $nbt1 = ($item1->getNamedTag() ?? new CompoundTag());
            
            foreach ($e->getTransaction()->getInventories() as $inv){
                if ($inv instanceof EnderChestInventory) {
                    if(
                        ($nbt->hasTag("EnderChestSlots")
                        && $nbt->getString("EnderChestSlots") == "Restricted")
                        || ($nbt1->hasTag("EnderChestSlots")
                        && $nbt1->getString("EnderChestSlots") == "Restricted")
                    )
                    {
                        $e->setCancelled();
                    }
                }
            }
        }
    }
    
    private function setSlots(Player $player, int $slots): void
    {
        $enderchest = $player->getEnderChestInventory();
        
        for ($i = 1; $i <= 26; $i++){
            $item = $player->getEnderChestInventory()->getItem($i);
            $nbt = ($item->getNamedTag() ?? new CompoundTag());
            
            if($nbt->hasTag("EnderChestSlots") && $nbt->getString("EnderChestSlots") === "Restricted"){
                $enderchest->setItem($i, Item::get(0, 0, 1), true);
            }
            
            if($slots <= $i){
                $config = $this->getConfig();
                
                $glass = Item::get(Item::STAINED_GLASS_PANE, 15, 1);
                $glass->setCustomName($config->getNested("restricted") ?? "");
                
                $nbt = ($glass->getNamedTag() ?? new CompoundTag());
                $nbt->setString("EnderChestSlots", "Restricted");
                $glass->setNamedTag($nbt);
                
                $enderchest->setItem($i, $glass);
                
                $slots++;	
            }
        }
    }
}