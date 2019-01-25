
<?php

namespace EnterTainerSK;

use pocketmine\{Server, Player};
use pocketmine\event\Listener as L;
use pocketmine\plugin\PluginBase as PB;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use jojoe77777\FormAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use EnterTainerSK\House;
use pocketmine\level\Level;
use pocketmine\level\sound\GhastSound;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;


Class House extends PB implements L{

  public $noperm = "§cYou do not have permissions to use this!.";

  function onEnable(){
   $this->getServer()->getPluginManager()->registerEvents($this, $this);
   $this->getLogger()->info("§l§cHouses by EnterTainerSK ENABLED!");
		$this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		
		@mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->getResource("config.yml");
    }

    public function checkDepends(){
        $this->formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        if(is_null($this->formapi)){
            $this->getLogger()->error("§4Please install FormAPI Plugin, disabling plugin...");
            $this->getPluginLoader()->disablePlugin($this);
        }
    }

	 public function onCommand(CommandSender $sender, Command $command, $label, array $args): bool {
  switch($command->getName()){
   case "house":
        if(!($sender instanceof Player)){
                $sender->sendMessage("§cPlease use this command from In-game!", false);
                return true;
        }
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data){
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
                    case 0:
                    $sender->sendMessage("Cancelled");
                        break;
                    case 1:
                    $this->Small($sender);
                        break;
                    case 2:
                    $this->Medium($sender);
                        break;
            }
        });
        $form->setContent("Choose if you want to buy any houses yourself.");
        $form->addButton("§cExit", 0);
        $form->addButton("Small house", 1);
        $form->addButton("Medium house", 2);
        $form->sendToPlayer($sender);
        }
        return true;
    }

    public function Small($sender){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createModalForm(function (Player $sender, $data){
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
                    case 1:
            $money = $this->eco->myMoney($sender);
		    $heal = $this->getConfig()->get("small.cost");
			if($money >= $heal){
				
               $this->eco->reduceMoney($sender, $heal);
		$inv = $sender->getInventory();
   	$small = Item::get(52, 0, 1)->setCustomName("§l§cHOUSE§r\n§r§7( §fSmall §7)§r");
	   $enchantment = Enchantment::getEnchantment(15);
	   $small->addEnchantment(new EnchantmentInstance($enchantment, 4));
      $inv->setItem(0, $small);
			   $sender->sendMessage("§7(§e!§7)§a You bought §csmall§a house from §e$2500!");
              return true;
            }else{
               $sender->sendMessage("§7(§e!§7)§a You do not have money for buy this house!");
            }
                        break;
                    case 2:
               $sender->sendMessage("§7(§c!§7)§c Shopping cancelled!");
                        break;
            }
        });
        $form->setTitle("§lSmall house");
        $form->setContent("§aDo you want buy this house from §e$2500§a?");
        $form->setButton1("Confirm", 1);
        $form->setButton2("Cancel", 2);
        $form->sendToPlayer($sender);
    }

    public function Medium($sender){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createModalForm(function (Player $sender, $data){
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
                    case 1:
		    $money = $this->eco->myMoney($sender);
		    $feed = $this->getConfig()->get("medium.cost");
			if($money >= $feed){
				
               $this->eco->reduceMoney($sender, $feed);
		$inv = $sender->getInventory();
   	$medium = Item::get(52, 0, 1)->setCustomName("§l§cHOUSE§r\n§r§7( §fmedium §7)§r");
	   $enchantment = Enchantment::getEnchantment(15);
	   $medium->addEnchantment(new EnchantmentInstance($enchantment, 4));
      $inv->setItem(1, $medium);
			   $sender->sendMessage("§7(§e!§7)§a You bought §cmedium§a house from §e$4000!");
              return true;
            }else{
               $sender->sendMessage("§7(§e!§7)§a You do not have money for buy this house!");
            }
                        break;
                    case 2:
               $sender->sendMessage("§7(§c!§7)§c Shopping cancelled!");
                    #If player click "NO" it will close the UI.
                        break;
            }
        });
        $form->setTitle("§lMedium house");
        $form->setContent("§aDo you want buy this house from §e$4000§a?");
        $form->setButton1("Confirm", 1);
        $form->setButton2("Cancel", 2);
        $form->sendToPlayer($sender);
    }

  function Houses(PlayerInteractEvent $ev){
   $p = $ev->getPlayer();
   $i = $p->getInventory()->getItemInHand();
   $inv = $p->getInventory();
   
   if($i->getId() == 151){
    $i = Item::get(151,0,1)->setCustomName("§r§l§cHOUSE");
    $small = Item::get(52,0,1)->setCustomName("§r§l§cHOUSE§r\n§r§7( §fSmall §7)§r");
	$enchantment = Enchantment::getEnchantment(15);
	$small->addEnchantment(new EnchantmentInstance($enchantment, 4));
    
    $inv->setItem(0, $small);
    }
   if($i->getId() == 52 && $i->getDamage() == 0){
   if($p->hasPermission("usehome.small")){
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 0, $p->getZ() -1), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 0, $p->getZ() -0), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 0, $p->getZ() -2), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 0, $p->getZ() +1), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 0, $p->getZ() +2), Block::get(43,3));
          $p->getLevel()->setBlock(new Vector3($p->getX() - 0, $p->getY() + 0, $p->getZ() +0), Block::get(44,3));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 0, $p->getZ() -1), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 0, $p->getZ() -0), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 0, $p->getZ() -2), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 0, $p->getZ() +1), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 0, $p->getZ() +2), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 0, $p->getZ() -1), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 0, $p->getZ() -0), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 0, $p->getZ() -2), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 0, $p->getZ() +1), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 0, $p->getZ() +2), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 0, $p->getZ() -1), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 0, $p->getZ() -0), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 0, $p->getZ() -2), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 0, $p->getZ() +1), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 0, $p->getZ() +2), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 0, $p->getZ() -1), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 0, $p->getZ() -0), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 0, $p->getZ() -2), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 0, $p->getZ() +1), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 0, $p->getZ() +2), Block::get(43,3)); 


         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 1, $p->getZ() -1), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 1, $p->getZ() -0), Block::get(0,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 1, $p->getZ() -2), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 1, $p->getZ() +1), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 1, $p->getZ() +2), Block::get(43,3));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 2, $p->getZ() -1), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 2, $p->getZ() -0), Block::get(0,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 2, $p->getZ() -2), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 2, $p->getZ() +1), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 2, $p->getZ() +2), Block::get(43,3));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 3, $p->getZ() -1), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 3, $p->getZ() -0), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 3, $p->getZ() -2), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 3, $p->getZ() +1), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 3, $p->getZ() +2), Block::get(43,3));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 4, $p->getZ() -1), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 4, $p->getZ() -0), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 4, $p->getZ() -2), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 4, $p->getZ() +1), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 4, $p->getZ() +2), Block::get(17,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 5, $p->getZ() -1), Block::get(85,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 5, $p->getZ() -0), Block::get(85,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 5, $p->getZ() -2), Block::get(85,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 5, $p->getZ() +1), Block::get(85,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 1, $p->getY() + 5, $p->getZ() +2), Block::get(85,0));


         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 1, $p->getZ() -2), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 1, $p->getZ() +2), Block::get(5,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 2, $p->getZ() -2), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 2, $p->getZ() +2), Block::get(5,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 3, $p->getZ() -2), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 3, $p->getZ() +2), Block::get(5,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 4, $p->getZ() -2), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 4, $p->getZ() +2), Block::get(17,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 5, $p->getZ() -2), Block::get(85,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 5, $p->getZ() +2), Block::get(85,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 4, $p->getZ() -1), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 4, $p->getZ() +1), Block::get(17,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 2, $p->getY() + 4, $p->getZ() -0), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 1, $p->getZ() -2), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 1, $p->getZ() +2), Block::get(5,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 2, $p->getZ() -2), Block::get(102,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 2, $p->getZ() +2), Block::get(102,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 3, $p->getZ() -2), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 3, $p->getZ() +2), Block::get(5,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 4, $p->getZ() -2), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 4, $p->getZ() +2), Block::get(17,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 5, $p->getZ() -2), Block::get(85,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 5, $p->getZ() +2), Block::get(85,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 4, $p->getZ() -1), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 4, $p->getZ() +1), Block::get(17,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 3, $p->getY() + 4, $p->getZ() -0), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 1, $p->getZ() -2), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 1, $p->getZ() +2), Block::get(5,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 2, $p->getZ() -2), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 2, $p->getZ() +2), Block::get(5,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 3, $p->getZ() -2), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 3, $p->getZ() +2), Block::get(5,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 4, $p->getZ() -2), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 4, $p->getZ() +2), Block::get(17,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 5, $p->getZ() -2), Block::get(85,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 5, $p->getZ() +2), Block::get(85,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 4, $p->getZ() -1), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 4, $p->getY() + 4, $p->getZ() +1), Block::get(17,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 1, $p->getZ() -1), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 1, $p->getZ() -0), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 1, $p->getZ() -2), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 1, $p->getZ() +1), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 1, $p->getZ() +2), Block::get(43,3));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 2, $p->getZ() -1), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 2, $p->getZ() -0), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 2, $p->getZ() -2), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 2, $p->getZ() +1), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 2, $p->getZ() +2), Block::get(43,3));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 3, $p->getZ() -1), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 3, $p->getZ() -0), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 3, $p->getZ() -2), Block::get(43,3)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 3, $p->getZ() +1), Block::get(5,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 3, $p->getZ() +2), Block::get(43,3));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 4, $p->getZ() -1), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 4, $p->getZ() -0), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 4, $p->getZ() -2), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 4, $p->getZ() +1), Block::get(17,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 4, $p->getZ() +2), Block::get(17,0));
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 5, $p->getZ() -1), Block::get(85,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 5, $p->getZ() -0), Block::get(85,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 5, $p->getZ() -2), Block::get(85,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 5, $p->getZ() +1), Block::get(85,0)); 
         $p->getLevel()->setBlock(new Vector3($p->getX() - 5, $p->getY() + 5, $p->getZ() +2), Block::get(85,0));


         $p->addTitle("§cHOUSE", "§9Your house placed!");
			$p->getLevel()->broadcastLevelSoundEvent($p->asVector3(), LevelSoundEventPacket::SOUND_LARGE_BLAST);
         }else{ $p->sendMessage($this->noperm); }
      }
  }
}


