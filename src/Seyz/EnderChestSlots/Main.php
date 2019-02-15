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
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;

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
	$player = $e->getPlayer();
	  
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
        	$item =$transaction->getSourceItem();
	        $nbt = ($item->getNamedTag() ?? new CompoundTag());
			$item1 =$transaction->getTargetItem();
	        $nbt1 = ($item1->getNamedTag() ?? new CompoundTag());
            foreach ($e->getTransaction()->getInventories() as $inv) {
                if ($inv instanceof EnderChestInventory) {
                	//Palente was here <3
	                //Make code in QuickEdit because no pc Make donation for me pls
                    if(($nbt->hasTag("EnderChestSlots", StringTag::class) && $nbt->getTagValue("EnderChestSlots", StringTag::class) == "HighLev") OR ($nbt1->hasTag("EnderChestSlots", StringTag::class) && $nbt1->getTagValue("EnderChestSlots", StringTag::class) == "HighLev"))
			    {
                        $e->setCancelled();
                    }
                }
            }
        }
    }
	
    private function setSlots(Player $player,int $slots) : void 
    {
    	$enderchest =$player->getEnderChestInventory();
	for ($i = 1; $i <= 26; $i++) {
		$item = $player->getEnderChestInventory()->getItem($i);
		$nbt = ($item->getNamedTag() ?? new CompoundTag());
	    if ($nbt->hasTag("EnderChestSlots", StringTag::class) && $nbt->getTagValue("EnderChestSlots", StringTag::class) == "HighLev") {

               $enderchest->setItem($i, Item::get(0, 0, 1), true);

             }

    	     if($slots <= $i) {
		
	   
		         $glass = Item::get(Item::STAINED_GLASS_PANE, 15, 1);
                 $glass->setCustomName("§4RESTRICTED");
                 $nbt = ($glass->getNamedTag() ?? new CompoundTag());
				 $nbt->setTag(new StringTag("EnderChestSlots", "HighLev"));
				 $glass->setNamedTag($nbt);
                 $enderchest->setItem($i, $glass);
                 
	         $slots++;	
		
             }

	 
	}
	    
    }
	
    private function isConfig () : bool
    {
         $cfg = $this->config->getAll();
	 
	 foreach ($cfg as $name => $number) {
		 
	    if($number > 26 or $number < 0){
		    
	        return false;
		break;
               
	    }

	 }
	    
	 return true;
	    
    }
	
}
