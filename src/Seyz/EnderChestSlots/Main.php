<?php

namespace Seyz\EnderChestSlots;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\EnderChestInventory;
use pocketmine\item\Item;

class Main extends PluginBase implements Listener {
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder());		
		if(!file_exists($this->getDataFolder()."config.yml")){
			$this->saveResource('config.yml');	
			}
		$this->config = new Config($this->getDataFolder().'config.yml', Config::YAML);
	}
	
	public function onOpenEnderchest(InventoryOpenEvent $e)
    {
        $inv = $e->getInventory();
        
        if($this->config->get("enderchest-slots") <= 26 and $this->config->get("enderchest-slots") >= 0){
        		if ($inv instanceof EnderChestInventory) {
            $slots = $this->config->get("enderchest-slots");
            while ($slots <= 26) {
                $glass = Item::get(Item::GLASS_PANE, 0, 1);
                $glass->setCustomName(" ");
                $inv->setItem($slots, $glass);
                $slots++;
            }
        }
    }
 }

    public function onEnderchestTransaction(InventoryTransactionEvent $e)
    {
        $transactions = $e->getTransaction()->getActions();

        foreach ($transactions as $transaction) {
            foreach ($e->getTransaction()->getInventories() as $inv) {
                if ($inv instanceof EnderChestInventory) {
                    if($transaction->getSourceItem()->getName() == " "){
                        $e->setCancelled();
                    }
                }
            }
        }
    }
}