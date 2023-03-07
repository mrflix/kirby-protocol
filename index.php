<?php

Kirby::plugin('mrflix/protocol', [
	'sections' => [
		'protocol' => [
			'computed' => [
				'days' => function(){
					$max = option('mrflix.protocol.limit', 400);
					$days = [];

					$protocol = $this->model()->protocol_store()->toStructure();

					if($protocol->count() == 0){
						$protocol = new Structure();
						$key = 0;
						foreach($this->model()->childrenAndDrafts() as $portal){
							foreach($portal->protocol_store()->yaml() as $item){
								$protocol->append($key, new Kirby\Cms\StructureObject([
									'content' => $item,
									'id'      => $key
								]));
								$key++;
							}
						}
					}

					$actions = option('mrflix.protocol.actions');

					if(!$actions){
						throw new Exception('No mrflix.protocol.actions are defined');
					}

					$dateFormat = 'l, j. F Y';

					if(option('date.handler') == 'intl'){
						$dateFormat = 'cccc, d. LLLL yyyy';
					}

					$i = 0;
					foreach($protocol->sortBy('timestamp', 'desc') as $data){
						if($i++ > $max) break;

						if(!array_key_exists($data->action()->value(), $actions)){
							throw new Exception('mrflix.protocol.actions entry for action "'. $data->action()->value() .'" is not defined');
						}

						$action = $actions[$data->action()->value()];

						$tmplData = [];

						foreach(['user', 'page', 'param1', 'param2'] as $key){
							if(is_string($data->$key()->value()) && Kirby\Uuid\Uuid::is($data->$key())){
								if($uuid = \Kirby\Uuid\Uuid::for($data->$key())){
									$model = $uuid->model();
									// file_put_contents('php://stderr', get_class($model).PHP_EOL);
									$label = $model->title()->h();
									$path = $model->panel()->path();
									if(get_class($model) == 'Kirby\Cms\User'){
										$label = $model->name()->h();
									} elseif(get_class($model) == 'Kirby\Cms\File'){
										$label = $model->filename();
										$path = $model->parent()->panel()->path() .'/'. $model->panel()->path();
									}
									$tmplData[$key] = '<a href="'. Panel::url() . $path .'">'. $label .'</a>';
								} else {
									$tmplData[$key] = $data->$key()->value();
								}
							} else {
								$tmplData[$key] = $data->$key()->value();
							}
						}

						if(is_callable($action['message'])){
							$message = $action['message']($data);
						} else {
							$message = Str::template($action['message'], $tmplData);
						}

						$detail = '';

						if(isset($action['detail'])){
							if(is_callable($action['detail'])){
								$detail = $action['detail']($data);
							} else {
								$detail = Str::template($action['detail'], $tmplData);
							}
						}

						$date = explode(' ', $data->timestamp()->value())[0];

						$row = [
							'time' => Str::date(strtotime($data->timestamp()), option('mrflix.protocol.time', 'HH:mm'), option('date.handler')),
							'icon' => $action['icon'],
							'message' => $message,
							'detail' => $detail
						];

						if(!isset($days[$date])){
							$days[$date] = [
								'date' => Str::date(strtotime($date), option('mrflix.protocol.date', $dateFormat), option('date.handler')),
								'rows' => []
							];
						}

						array_push($days[$date]['rows'], array_merge($row));
					}

					return $days;
				}
			]
		]
	],
	'pageMethods' => [
		'protocol' => function($action, $param1 = '', $param2 = '') {
			$protocol = $this->protocol_store()->yaml();

			if(is_callable(option('mrflix.protocol.user')) === true){
				$user = option('mrflix.protocol.user')($this);
			} else {
				$user = $this->kirby()->user();
			}

			if(is_object($user) === true){
				if(get_class($user) === 'Kirby\Cms\Field'){
					$user = $user->value();
				} elseif(get_class($user) == 'Kirby\Cms\User'){
					$user = $user->uuid()->toString();
				}
			}

			if(is_object($param1) === true){
				if(get_class($param1) === 'Kirby\Cms\Field'){
					$param1 = $param1->value();
				} elseif($param1 instanceof Kirby\Cms\ModelWithContent){ // file, user, page
					$param1 = $param1->uuid()->toString();
				}
			}

			if(is_object($param2) === true){
				if(get_class($param2) === 'Kirby\Cms\Field'){
					$param2 = $param2->value();
				} elseif($param2 instanceof Kirby\Cms\ModelWithContent){
					$param2 = $param2->uuid()->toString();
				}
			}

			$data = [
				'timestamp' => date('Y-m-d H:i:s'),
				'user' => $user,
				'action' => $action,
				'page' => $this->uuid()->toString(),
				'param1' => $param1,
				'param2' => $param2
			];

			array_unshift($protocol, $data);

			$this->update(['protocol_store' => $protocol]);
		}
	]
]);
