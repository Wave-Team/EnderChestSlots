<?php

namespace Seyz\EnderChestSlots;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\EnderChestInventory;
use pocketmine\item\Item;
use pocketmine\Player;

class Main extends PluginBase implements Listener {
	
 public function onEnable(){
     $this->getServer()->getPluginManager()->registerEvents($this, $this);
     @mkdir($this->getDataFolder());		
     if(!file_exists($this->getDataFolder()."config.yml")){
         $this->saveResource('config.yml');	
     }
     $this->config = new Config($this->getDataFolder().'config.yml', Config::YAML);
	 
     if (!$this->isConfig()) {

	  $this->getLogger()->critical("The config.yml file is not good, please check if the slots are between 0 and 26");
	     
     }
	 
  }
	
  public function onOpenEnderchest(InventoryOpenEvent $e)
  {
        $inv = $e->getInventory();
	$player = $e->getPLayer();
	  
        if ($inv instanceof EnderChestInventory) {
		
	    if ($player->hasPermission("enderchest.staff.perm")) {

                $this->setSlots($player, $this->config->get("enderchest-slots-staff"));

            } else if ($player->hasPermission("enderchest.vip+.perm")) {

                $this->setSlots($player, $this->config->get("enderchest-slots-vip+"));

            } else if ($player->hasPermission("enderchest.vip.perm")) {

                $this->setSlots($player, $this->config->get("enderchest-slots-vip"));

            } else if ($player->hasPermission("enderchest.yt.perm")) {

                $this->setSlots($player, $this->config->get("enderchest-slots-yt"));

            }  else {

                $this->setSlots($player, $this->config->get("enderchest-slots-default"));

            }
		
        }
	  
    }

    public function onEnderchestTransaction(InventoryTransactionEvent $e)
    {
        $transactions = $e->getTransaction()->getActions();

        foreach ($transactions as $transaction) {
            foreach ($e->getTransaction()->getInventories() as $inv) {
                if ($inv instanceof EnderChestInventory) {
                    if($transaction->getSourceItem()->getName() == " " &&
		       $transaction->getSourceItem()->getId() == Item::STAINED_GLASS_PANE &&
		       $transaction->getSourceItem()->getDamage() == 15
		      or
		       $transaction->getTargetItem()->getName() == " " &&
		       $transaction->getTargetItem()->getId() == Item::STAINED_GLASS_PANE &&
		       $transaction->getTargetItem()->getDamage() == 15)
		    {
                        $e->setCancelled();
                    }
                }
            }
        }
    }
	
    private function setSlots(Player $player,int $slots) : void 
    {
	for ($i = 1; $i <= 26; $i++) {

	    if ($player->getEnderChestInventory()->getItem($i)->getId() === Item::STAINED_GLASS_PANE &&
               $player->getEnderChestInventory()->getItem($i)->getDamage() === 15 &&
               $player->getEnderChestInventory()->getItem($i)->getCustomName() === " ") {

               $player->getEnderChestInventory()->setItem($i, Item::get(0, 0, 1), true);

             }

    	     if($slots <= $i) {
		
	   
	         $glass = Item::get(Item::STAINED_GLASS_PANE, 15, 1);
                 $glass->setCustomName(" ");
                 $player->getEnderChestInventory()->setItem($i, $glass);
	         $slots++;	
		
             }

	 
	}
	    
    }
	
    private function isConfig () : bool
    {
         $cfg = $this->config->getAll();
	 
	 foreach ($cfg as $name => $number) {
		 
	    if($number > 26 or $number < 0){
		    
	    	#fail maybe
	        return false;
		break;
               
	    }

	 }
	    
	 return true;
	    
    }
	
}
