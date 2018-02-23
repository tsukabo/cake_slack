<?php

App::uses('HttpSocket', 'Network/Http');

class Slack
{
	protected static $_client = null;
	protected static $_settings = null;

	protected static function _getClient() {
		if (static::$_client === null) {
			static::$_client = new HttpSocket(array(
				'ssl_verify_host' => false,
			));
		}

		static::$_client->request['header'] = [];
		return static::$_client;
	}

	public static function settings($key) {
		if (static::$_settings === null) {
			$settings = [
				'channel' => '#general',
				'username' => 'cakephp',
				'icon_emoji' => ':ghost:',
			];

			static::$_settings = array_merge($settings, Configure::read('Slack'));
		}
		return static::$_settings[$key];
	}

	public static function send($message)
	{
		$client = static::_getClient();
		$payload = [
			'channel' => static::settings('channel'),
			'username' => static::settings('username'),
			'text' => $message,
			'icon_emoji' => static::settings('icon_emoji'),
		];

		$token = static::settings('token');
		$uri = "https://hooks.slack.com/services/{$token}";
		$request = [
			'header' => [
				'Content-Type' => 'application/json',
			]
		];

		// slack側に問題があり通知出来ない場合のためにlogを落とす
		try {
			$response = $client->post($uri, json_encode($payload), $request);

			if ($response->code !== 200 || $response->body !== 'ok') {
				return false;
			}
		} catch (Exception $e) {
			CakeLog::write('error', $e->getMessage());
			return false;
		}


		return true;
	}
}
