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
		});
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
			'line_items' => array($line_item),
			'success_url' => home_url('/cv-generator?stripe_session_id={CHECKOUT_SESSION_ID}'),
			'cancel_url' => home_url('/cv-generator?stripe_session_id={CHECKOUT_SESSION_ID}'),
		));

		// Redirect the customer to the Checkout page
		header('Location: ' . $session->url);
		exit();
    }

	public function processPayment() {
		if (!isset($_GET['stripe_session_id'])) {
			return false;
		}

		try {
			$session = $this->stripe->checkout->sessions->retrieve($_GET['stripe_session_id']);
			$payment_intent = $this->stripe->paymentIntents->retrieve($session->payment_intent);
			$customer = $this->stripe->customers->retrieve($session->customer);
			$shouldBeUserEmail = $customer->email;
			$user = wp_get_current_user();
			if (!$user->ID) {
				die('You must be logged in to use this feature.');
			}
			if ($user->user_email !== $shouldBeUserEmail) {
				if (WP_DEBUG) {
					// wp die to show that user email did not match
					die("User email did not match. $user->user_email != $shouldBeUserEmail");
				}
				return ['status' => 'fail', 'message' => __("Error with the payment 200-100", 'cv-generator')];
			}
			if ($payment_intent->status === 'succeeded') {
				$this->setUserPaidStatus( true );

				return [ 'status'  => 'ok', 'message' => __( "Thanks for your order, now you can download your CV", 'cv-generator' ) ];
			} else {
				return ['status' => 'fail','message' => __("Payment did not succeed (10002)", 'cv-generator')];
			}
		} catch (Throwable $e) {
			if (WP_DEBUG) {
				die($e->getMessage());
			}
			return ['status' => 'fail', 'message' => __("Error with the payment 200-200.", 'cv-generator')];
		}
	}

	public function setUserPaidStatus(bool $status) {
		$userId = get_current_user_id();
		if (!$userId) {
			return;
		}

        update_user_meta($userId, 'cvgenerator_paid', $status ? 1 : 0);
        update_user_meta($userId, 'cvgenerator_paid_at', time());
    }

	public static function getCurrentUserHowManyLeftMinutes($userBackupId = false) {
		$userId = get_current_user_id();
		if (!$userId && !$userBackupId) {
			return false;
		}

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