<?php
namespace delion\FlowyTest;

use pocketmine\event\player\{PlayerEvent, PlayerJoinEvent, PlayerJumpEvent, PlayerQuitEvent};
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use flowy\Flowy;
use flowy\standard\Standard;
use function flowy\{listen, start};
use function flowy\standard\delay;

class Main extends PluginBase {
  function onEnable() {
    Flowy::bootstrap();
    Standard::bootstrap();

    $stream = start($this);
    $stream->run(function($stream) {
      $this->getLogger()->info("sleeping...");
      yield from delay($this->getScheduler(), 20 * 10);
      $this->getLogger()->info("Hello!!");

      while(true) {
        $event = yield listen(PlayerJoinEvent::class);
        $player = $event->getPlayer();

        $playerStream = $stream->filter(function($ev) use ($player) {
          return !($ev instanceof PlayerEvent) || $ev->getPlayer() === $player;
        })->stream();

        $playerStream->run(function($stream) {
          $event = yield listen(PlayerQuitEvent::class);
          $stream->close();
        });
        $playerStream->run(function($stream) use ($player) {
          while(true) {
            $event = yield listen(PlayerJumpEvent::class);
            yield from delay($this->getScheduler(), 20 * 3);
            $player->sendMessage("rejump!!");
            $player->setMotion(new Vector3(0, 1, 0));
          }
        });
      }
    });
  }
}
