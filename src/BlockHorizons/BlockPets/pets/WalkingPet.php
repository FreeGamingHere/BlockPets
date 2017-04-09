<?php

namespace BlockHorizons\BlockPets\pets;

use pocketmine\block\Slab;
use pocketmine\block\Stair;

abstract class WalkingPet extends BasePet {

	public function onUpdate($currentTick) {
		$petOwner = $this->getPetOwner();
		parent::onUpdate($currentTick);
		if($petOwner === null || $this->isRidden()) {
			return false;
		}
		if(!$this->isOnGround()) {
			if($this->motionY > -$this->gravity * 4) {
				$this->motionY = -$this->gravity * 4;
			} else {
				$this->motionY -= $this->gravity;
			}
		} elseif($this->isCollidedHorizontally) {
			$this->jump();
		}

		$x = $petOwner->x - $this->x;
		$z = $petOwner->z - $this->z;

		if($x * $x + $z * $z < 5) {
			$this->motionX = 0;
			$this->motionZ = 0;
		} else {
			$this->motionX = $this->getSpeed() * 0.15 * ($x / (abs($x) + abs($z)));
			$this->motionZ = $this->getSpeed() * 0.15 * ($z / (abs($x) + abs($z)));
		}
		$this->yaw = rad2deg(atan2(-$x, $z));
		$this->pitch = rad2deg(-atan($petOwner->y - $this->y));

		$this->move($this->motionX, $this->motionY, $this->motionZ);
		$this->updateMovement();
		return true;
	}

	public function doRidingMovement($currentTick) {
		$rider = $this->getPetOwner();

		$this->pitch = $rider->pitch;
		$this->yaw = $rider->yaw;
		if(!$this->isOnGround()) {
			if($this->motionY > -$this->gravity * 4) {
				$this->motionY = -$this->gravity * 4;
			} else {
				$this->motionY -= $this->gravity;
			}
		} elseif($this->isCollidedHorizontally) {
			$this->jump();
		}

		$x = $rider->getDirectionVector()->x;
		$z = $rider->getDirectionVector()->z;

		$this->motionX = $this->getSpeed() * 0.4 * ($x / (abs($x) + abs($z)));
		$this->motionZ = $this->getSpeed() * 0.4 * ($z / (abs($x) + abs($z)));

		$this->move($this->motionX, $this->motionY, $this->motionZ);
		$this->checkBlockCollision();

		$this->updateMovement();
	}

	protected function jump() {
		$solidCount = 0;
		$halfCount = 0;
		$positionsToCheck = [
			$this->add(1, 0, 0),
			$this->add(1, 0, 1),
			$this->add(0, 0, 1),
			$this->add(-1, 0, 1),
			$this->add(-1, 0, 0),
			$this->add(-1, 0, -1),
			$this->add(0, 0, -1),
			$this->add(1, 0, -1)
		];
		foreach($positionsToCheck as $position) {
			$blockAhead = $this->getLevel()->getBlock($position);
			if($this->getLevel()->getBlock($blockAhead->add(0, 1))->isSolid()) {
				continue;
			}
			if($blockAhead->isSolid()) {
				$solidCount++;
				continue;
			} elseif($blockAhead instanceof Slab || $blockAhead instanceof Stair) {
				$halfCount++;
				continue;
			}
		}
		if($solidCount >= 3) {
			$this->motionY = 0.8;
			$this->move($this->motionX, $this->motionY, $this->motionZ);
			return;
		} elseif($halfCount >= 3) {
			$this->motionY = 0.4;
			$this->move($this->motionX, $this->motionY, $this->motionZ);
			return;
		}
	}
}
