<?php

/*
	InstaPerms by the BoxOfDevs Team (boxofdevs.com)
	Copyright © 2017 BoxOfDevs Team - BoxOfDevs General Software License 1.1.2
*/

namespace InstaPerms;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as C;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\permission\Permission;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\IPlayer;
use pocketmine\Server;

class Main extends PluginBase implements CommandExecutor {
	
	const PREFIX = C::BLACK."[".C::AQUA."InstaPerms".C::BLACK."]".C::WHITE." ";

	public function onEnable(){
		$this->getLogger()->info(self::PREFIX.C::GREEN."Enabled!");
		$this->data = new Config($this->getDataFolder()."/data.yml", Config::YAML);
		$this->data->save();
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$pname = $player->getName();
		if(isset($this->data->getAll()[$pname])){
			$data = $this->data->getAll()[$pname];
			foreach($data as $perm){
				$perm = Server::getInstance()->getPluginManager()->getPermission($perm);
				$player->addAttachment($this, $perm, true);
			}
		}
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		switch($cmd){
			case "setperm":
			if(!isset($args[1])){
				$sender->sendMessage(self::PREFIX.C::DARK_RED."Usage: /setperm <player> <permission>");
			}else{
				$playername = $args[0];
				$this->data->set($playername, array_push($this->data->get($playername), $args[1]));
				$this->data->save();
				$player = $this->getServer()->getPlayer($playername);
				$perm = Server::getInstance()->getPluginManager()->getPermission($args[1]);
				$player->addAttachment($this, $perm, true);
				$sender->sendMessage(self::PREFIX.C::GREEN.$perm." successfully set to ".$playername.".");
			}
			return true;
			case "rmperm":
			if(!isset($args[1])){
				$sender->sendMessage(self::PREFIX.C::DARK_RED."Usage: /rmperm <player> <permission>");
			}else{
				$playername = $args[0];
				$currentPerms = $this->data->get($playername);
				foreach(array_keys($currentPerms, $args[1]) as $key){
					unset($currentPerms[$key]);
				}
				$this->data->set($playername, $currentPerms);
				$this->data->save();
				$player = $this->getServer()->getPlayer($playername);
				$perm = Server::getInstance()->getPluginManager()->getPermission($args[1]);
				$player->removeAttachment($this, $perm, true);
				$sender->sendMessage(self::PREFIX.C::GREEN.$perm." removed from ".$playername."!");
			}
			return true;
			case "seeperms":
			if(!isset($args[0])){
				$sender->sendMessage(self::PREFIX.C::DARK_RED."Usage: /seeperms <player>");
			}else{
				$playername = $args[0];
				$player = $this->getServer()->getPlayer($playername);
				$perms = $player->getEffectivePermissions();
				$plperms = [];
				foreach($perms as $perm){
					array_push($plperms, $perm->getPermission());
				}
				$sender->sendMessage(self::PREFIX.C::GOLD.$playername."'s permissions: \n".C::AQUA . implode(", ", $plperms));
			}
			return true;
			case "hasperm":
			if(!isset($args[1])){
				$sender->sendMessage(self::PREFIX.C::DARK_RED."Usage: /hasperm <player> <permission>");
			}else{
				$playername = $args[0];
				$player = $this->getServer()->getPlayer($playername);
				$perm = $args[1];
				if($player->hasPermission($perm)){
					$sender->sendMessage(self::PREFIX.C::AQUA.$playername.C::GOLD." has permission ".C::GREEN.$perm.C::GRAY.".");
				}else{
					$sender->sendMessage(self::PREFIX.C::AQUA.$playername.C::RED." doesn't have permission ".C::GREEN.$perm.C::GRAY.".");
				}
			}
			return true;
		}
		return true;
	}
	
	public function onDisable(){
		$this->getLogger()->info(self::PREFIX.C::DARK_RED."Disabled!");
	}
}