<?php

namespace Seyz\EnderChestSlots;

use pocketmine\block\inventory\EnderChestInventory;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{
    
    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource("config.yml");

        if ($this->getConfig()->exists("permission")) {
            foreach ($this->getConfig()["permission"] as $data) {
                if (isset($data["permission"])) {
                    PermissionManager::getInstance()->addPermission(new Permission($data["permission"]));
                }
            }
        }
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
                        ($nbt->getTag("EnderChestSlots")
                        && $nbt->getString("EnderChestSlots") == "Restricted")
                        || ($nbt1->getTag("EnderChestSlots")
                        && $nbt1->getString("EnderChestSlots") == "Restricted")
                    )
                    {
                        $e->cancel();
                    }
                }
            }
        }
    }
    
    private function setSlots(Player $player, int $slots): void
    {
        $enderchest = $player->getEnderInventory();
        
        for ($i = 1; $i <= 26; $i++){
            $item = $player->getEnderInventory()->getItem($i);
            $nbt = ($item->getNamedTag() ?? new CompoundTag());
            
            if($nbt->getTag("EnderChestSlots") && $nbt->getString("EnderChestSlots") === "Restricted"){
                $enderchest->setItem($i, VanillaItems::AIR());
            }
            
            if($slots <= $i){
                $config = $this->getConfig();
                
                $glass = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GRAY())->asItem();
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