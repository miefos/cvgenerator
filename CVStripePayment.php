<?php

require_once "CVSettings.php";

class CVStripePayment {
	private $stripe;

	public function __construct(CVSettings $settings) {
		$this->settings = $settings;

		// get payments settings
		$this->price = $this->settings->get_settings()['payments']['price_1'] ?? '';
		$this->product_name = $this->settings->get_settings()['payments']['product_name_1'] ?? '';
		$this->product_description = $this->settings->get_settings()['payments']['product_description_1'] ?? '';
		$this->apiKey = $this->settings->get_settings()['payments']['stripe_api_key'] ?? '';
		$this->webhook_secret = $this->settings->get_settings()['payments']['stripe_webhook_secret'] ?? '';

		// set up Stripe
		if ($this->apiKey) {
			$this->stripe = new \Stripe\StripeClient($this->apiKey);
			\Stripe\Stripe::setApiKey($this->apiKey);
		}

		// register route
		add_action('rest_api_init', function() {
			register_rest_route( CVGEN_REST_PAYMENT_API_URL[0], CVGEN_REST_PAYMENT_API_URL[1], array(
				'methods' => 'GET',
				'callback' => [$this, 'redirect_to_stripe'],
				'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
			) );

			register_rest_route( CVGEN_REST_WEBHOOK_URL[0], CVGEN_REST_WEBHOOK_URL[1], array(
				'methods' => 'POST',
				'callback' => [$this, 'process_webhook'],
				'current_user_id' => get_current_user_id(), // This will be pass to the rest API callback
			) );
		});
	}

	public function process_webhook() {
		// This is your Stripe CLI webhook secret for testing your endpoint locally.
		$endpoint_secret = $this->webhook_secret;

		$payload = @file_get_contents('php://input');
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
		$event = null;

		try {
			$event = \Stripe\Webhook::constructEvent(
				$payload, $sig_header, $endpoint_secret
			);
		} catch(\UnexpectedValueException $e) {
			// Invalid payload
			http_response_code(400);
			exit();
		} catch(\Stripe\Exception\SignatureVerificationException $e) {
			// Invalid signature
			http_response_code(400);
			exit();
		}

		switch ($event->type) {
			case 'checkout.session.async_payment_succeeded':
				$session = $event->data->object;
			case 'checkout.session.completed':
				$session = $event->data->object;
			default:
				echo 'Received unknown event type ' . $event->type;
		}

		if (!isset($session)) {
			http_response_code(400);
			die('could not get session object');
		}

		$userEmail = $session->customer_details->email;
		$user = get_user_by('email', $userEmail);
		if (!$user) {
			http_response_code(404);
            die('could not find user');
		}

		$this->setUserPaidStatus($user->ID,true);

		http_response_code(200);
		return 'ok';
	}

	public function redirect_to_stripe($data) {
		$current_user_id = $data->get_attributes()['current_user_id']; // !! this comes from php not js
		$user = get_user_by('ID', $current_user_id);
		if (!$user) {
			wp_die('You must be logged in to use this feature.');
		}

		if (empty($this->apiKey)) {
			wp_die('Invalid Stripe API key.');
		}

		// Create or retrieve the customer object
		$customer = $this->stripe->customers->create(array(
			'email' => $user->user_email,
		));

		// set up line item
		$line_item = array(
			'name' => $this->product_name,
			'amount' => $this->price * 100,
			'currency' => 'eur',
			'quantity' => 1,
		);

		if (!empty($this->product_description)) {
			$line_item['description'] = $this->product_description;
		}

		// Create a Checkout session
		$session = \Stripe\Checkout\Session::create(array(
			'customer' => $customer->id,
			'payment_method_types' => array('card'),
			'mode' => 'payment',
			'metadata' => ['cvgen_used' => 0],
			'line_items' => array($line_item),
			'success_url' => home_url('/cv-generator'),
			'cancel_url' => home_url('/cv-generator'),
		));

		// Redirect the customer to the Checkout page
		header('Location: ' . $session->url);
		exit();
    }

	public function setUserPaidStatus($userId, bool $status) {
        update_user_meta($userId, 'cvgenerator_paid', $status ? 1 : 0);
        update_user_meta($userId, 'cvgenerator_paid_at', time());
    }

	public static function getCurrentUserHowManyLeftMinutes($userBackupId = false) {
		$userId = get_current_user_id();

		if (!$userId && !$userBackupId) {
			return false;
		}

		$userId = $userId ?: $userBackupId;

		$expireInHours = CVSettings::getExpirationTimeInHours();

		$paid = get_user_meta($userId, 'cvgenerator_paid', true);
		$paid_at = get_user_meta($userId, 'cvgenerator_paid_at', true);

		if ($paid && $paid_at) {
			$expiration_time = strtotime('+' . $expireInHours . ' hours', $paid_at);

			// Calculate the difference between the expiration time and the current time in seconds
			$diff_seconds = $expiration_time - time();
			// Convert the difference into hours
			$diff_minutes = round($diff_seconds / 60);

			if (time() > $expiration_time) {
				return false;
			} else {
				return $diff_minutes;
			}
		}

		return false;
	}
}