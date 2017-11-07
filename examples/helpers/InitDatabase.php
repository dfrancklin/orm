<?php

namespace App\Helpers;

use ORM\Interfaces\IEntityManager;

class InitDatabase 
{

	private $data = [];
	
	public function __construct() {
		// $this->data['niveis'] = $this->loadFromFile('niveis.xpto');
	}
	
	public function beforeDrop(IEntityManager $em)
	{
		// $this->data['alunos'] = $em->list(App\Models\RFID\Alunos::class);
		vd('backing up...');
	}

	public function afterCreate(IEntityManager $em)
	{
		vd('initializating...');
	}

}