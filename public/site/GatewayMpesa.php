<?php

include('api.php');

class GatewayMpesa extends PaymentGateway
{
    private $apiKey;
	private $isTest;

	protected $returnAfterCallback = true;

    const PUBLIC_KEY_SANDBOX = "MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEArv9yxA69XQKBo24BaF/D+fvlqmGdYjqLQ5WtNBb5tquqGvAvG3WMFETVUSow/LizQalxj2ElMVrUmzu5mGGkxK08bWEXF7a1DEvtVJs6nppIlFJc2SnrU14AOrIrB28ogm58JjAl5BOQawOXD5dfSk7MaAA82pVHoIqEu0FxA8BOKU+RGTihRU+ptw1j4bsAJYiPbSX6i71gfPvwHPYamM0bfI4CmlsUUR3KvCG24rB6FNPcRBhM3jDuv8ae2kC33w9hEq8qNB55uw51vK7hyXoAa+U7IqP1y6nBdlN25gkxEA8yrsl1678cspeXr+3ciRyqoRgj9RD/ONbJhhxFvt1cLBh+qwK2eqISfBb06eRnNeC71oBokDm3zyCnkOtMDGl7IvnMfZfEPFCfg5QgJVk1msPpRvQxmEsrX9MQRyFVzgy2CWNIb7c+jPapyrNwoUbANlN8adU1m6yOuoX7F49x+OjiG2se0EJ6nafeKUXw/+hiJZvELUYgzKUtMAZVTNZfT8jjb58j8GVtuS+6TM2AutbejaCV84ZK58E2CRJqhmjQibEUO6KPdD7oTlEkFy52Y1uOOBXgYpqMzufNPmfdqqqSM4dU70PO8ogyKGiLAIxCetMjjm6FCMEA3Kc8K0Ig7/XtFm9By6VxTJK1Mg36TlHaZKP6VzVLXMtesJECAwEAAQ==";
    const PUBLIC_KEY_API = "MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAietPTdEyyoV/wvxRjS5pSn3ZBQH9hnVtQC9SFLgM9IkomEX9Vu9fBg2MzWSSqkQlaYIGFGH3d69Q5NOWkRo+Y8p5a61sc9hZ+ItAiEL9KIbZzhnMwi12jUYCTff0bVTsTGSNUePQ2V42sToOIKCeBpUtwWKhhW3CSpK7S1iJhS9H22/BT/pk21Jd8btwMLUHfVD95iXbHNM8u6vFaYuHczx966T7gpa9RGGXRtiOr3ScJq1515tzOSOsHTPHLTun59nxxJiEjKoI4Lb9h6IlauvcGAQHp5q6/2XmxuqZdGzh39uLac8tMSmY3vC3fiHYC3iMyTb7eXqATIhDUOf9mOSbgZMS19iiVZvz8igDl950IMcelJwcj0qCLoufLE5y8ud5WIw47OCVkD7tcAEPmVWlCQ744SIM5afw+Jg50T1SEtu3q3GiL0UQ6KTLDyDEt5BL9HWXAIXsjFdPDpX1jtxZavVQV+Jd7FXhuPQuDbh12liTROREdzatYWRnrhzeOJ5Se9xeXLvYSj8DmAI4iFf2cVtWCzj/02uK4+iIGXlX7lHP1W+tycLS7Pe2RdtC2+oz5RSSqb5jI4+3iEY/vZjSMBVk69pCDzZy4ZE8LBgyEvSabJ/cddwWmShcRS+21XvGQ1uXYLv0FCTEHHobCfmn2y8bJBb/Hct53BaojWUCAwEAAQ==";

	const COUNTRY_GHA = 'GHA';
	const COUNTRY_TZN = 'TZN';
	const COUNTRY_LES = 'LES';
	const COUNTRY_DRC = 'DRC';

	const MARKETS = [
		self::COUNTRY_GHA => [
			'name' => 'vodafoneGHA',
			'currency' => 'GHS'
		],
		self::COUNTRY_TZN => [
			'name' => 'vodacomTZN',
			'currency' => 'TZS'
		],
		self::COUNTRY_LES => [
			'name' => 'vodacomLES',
			'currency' => 'LSL'
		],
		self::COUNTRY_DRC => [
			'name' => 'vodacomDRC',
			'currency' => 'USD'
		],
	];

	const COUNTRY_GHA_SHORT = 'GH';
	const COUNTRY_TZN_SHORT = 'TZ';
	const COUNTRY_LES_SHORT = 'LS';
	const COUNTRY_DRC_SHORT = 'CD';

	const COUNTRY_SHORT_TO_NORMAL = [
		self::COUNTRY_GHA_SHORT => self::COUNTRY_GHA,
		self::COUNTRY_TZN_SHORT => self::COUNTRY_TZN,
		self::COUNTRY_LES_SHORT => self::COUNTRY_LES,
		self::COUNTRY_DRC_SHORT => self::COUNTRY_DRC,
	];

	const PROVIDER_CODE = '000000';

    public function init()
	{
		if (!empty($this->config->apiKey)) {
			$this->apiKey = $this->config->apiKey;
		}

		if (!empty($this->config->isTest)) {
			$this->isTest = $this->config->isTest;
		}
	}

	/**
	 * @return mixed|string|null
	 */
	public function getTransactionId()
	{
		if (!is_null(_get('transactionID'))) {
			return _get('transactionID');
		}

		return null;
	}

	/**
	 * @param array $formVars
	 * @return bool|string[]
	 */
	public function createFormFields($formVars)
	{
		return false;
	}

    private function generateSession($publicKey, $apiKey, $marketName, $isTest = false) 
	{
		// Create Context with API to request a Session ID
		$context = new APIContext();
		// Api key
		$context->set_api_key($apiKey);
		// Public key
		$context->set_public_key($publicKey);
		// Use ssl/https
		$context->set_ssl(true);
		// Method type (can be GET/POST)
		$context->set_method_type(APIMethodType::GET);
		// API address
		$context->set_address('openapi.m-pesa.com');
		// API Port
		$context->set_port(443);

        $url = $isTest ? 
            '/sandbox/ipg/v2/' . $marketName .'/getSession/' : 
            '/openapi/ipg/v2/' . $marketName . '/getSession/';

		// API Path
		$context->set_path($url);

		// Add/update headers
		$context->add_header('Origin', '*');

		// Parameters can be added to the call as well that on POST will be in JSON format and on GET will be URL parameters
		// context->add_parameter('key', 'value');

		// Create a request object
		$request = new APIRequest($context);

		try {
			$response = $request->execute();
		}
		catch (\Exception $e) {
			return [
				'error' => $e->getMessage()
			];
		}

        $body = $response->get_body();

		$result = json_decode($body, true);
		if (!is_array($result)) {
			return [
				'error' => $response->get_status_code() . ' ' . $body
			];
		}

        if (empty($result['output_SessionID']) && !empty($result['output_ResponseDesc'])) {
            return [
				'error' => $result['output_ResponseDesc']
			];
        }
		elseif (empty($result['output_SessionID'])) {
			return [
				'error' => SiteModule::__('SessionId getting failed')
			];
		}
		else {
			return [
				'SessionID' => $result['output_SessionID']
			];
		}

		return [
			'error' => SiteModule::__('SessionId getting failed')
		];
	}

    public function createOrderRequest()
	{
        $post = json_decode(file_get_contents('php://input'), true);

        $publicKey = $this->isTest ? self::PUBLIC_KEY_SANDBOX : self::PUBLIC_KEY_API;
		$apiKey = $this->apiKey;

		$country = isset($post['country']) ? $post['country'] : 'GHA';
		$market = self::MARKETS[$country];

		$price = isset($post['price']) ? $post['price'] : '';
		$phone = preg_replace("/[^0-9]/", '', isset($post['phone']) ? $post['phone'] : '');
		if ($post['isStore']) {
			$itemName = 'Store Order';
		}
		else {
			$itemName = isset($post['itemName']) ? $post['itemName'] : '';
		}

		if (empty($phone) || strlen($phone) < 12 || strlen($phone) > 14) {
			return [
				'error' => SiteModule::__('The phone number must be 12-14 digits.')
			];
		}

		// Get session key
		$sessionID = $this->generateSession($publicKey, $apiKey, $market['name'], $this->isTest);
		if (!empty($sessionID['error'])) {
			return [
				'error' => $sessionID['error']
			];
		}

		if (empty($sessionID['SessionID'])) {
			return [
				'error' => json_encode($sessionID)
			];
		}

		$sessionID = $sessionID['SessionID'];

		// Create Context with API to request a Session ID
		$context = new APIContext();
		// Session key
		$context->set_api_key($sessionID);
		// Public key
		$context->set_public_key($publicKey);

		// Use ssl/https
		$context->set_ssl(true);
		// Method type (can be GET/POST/PUT)
		$context->set_method_type(APIMethodType::POST);
		// API address
		$context->set_address('openapi.m-pesa.com');
		// API Port
		$context->set_port(443);

        $url = $this->isTest ? 
            '/sandbox/ipg/v2/' . $market['name'] . '/c2bPayment/singleStage/' : 
            '/openapi/ipg/v2/' . $market['name'] . '/c2bPayment/singleStage/';

		// API Path
		$context->set_path($url);

		// Add/update headers
		$context->add_header('Origin', '*'); 

		// Parameters can be added to the call as well that on POST will be in JSON format and on GET will be URL parameters
		// $context->add_parameter('key', 'value');

		$providerCode = $this->isTest ? self::PROVIDER_CODE : (isset($post['providerCode']) ? $post['providerCode'] : self::PROVIDER_CODE);

		$context->add_parameter('input_Country', $country);
		$context->add_parameter('input_PurchasedItemsDesc', $itemName);
		$context->add_parameter('input_Amount', $price);
		$context->add_parameter('input_CustomerMSISDN', $phone);
		$context->add_parameter('input_Currency', $market['currency']);
		$context->add_parameter('input_ServiceProviderCode', $providerCode);
        $context->add_parameter('input_ThirdPartyConversationID', isset($post['transactionId']) ? $post['transactionId'] : uniqid());
        $context->add_parameter('input_TransactionReference', isset($post['transactionId']) ? $post['transactionId'] : uniqid());

		// Create a request object
		$request = new APIRequest($context);

		try {
			$response = $request->execute();
		}
		catch (\Exception $e) {
			return [
				'error' => $e->getMessage()
			];
		}

		$body = $response->get_body();
		if ($body === null) {
			return [
				'error' => SiteModule::__('Unknown error')
			];
		}

		$body = json_decode($body, true);
		if (empty($body['output_ResponseCode']) || empty($body['output_ResponseDesc']) || empty($body['output_ConversationID'])) {
			return [
				'error' => SiteModule::__('Unknown error')
			];
		}

		if ($body['output_ResponseCode'] !== 'INS-0') {
			return [
				'error' => $body['output_ResponseDesc'],
				'transactionID' => $body['output_ConversationID']
			];
		}

		if (isset($post['callbackUrl']) && isset($post['transactionId'])) {
			$post['callbackUrl'] = $post['callbackUrl'] . '?transactionID=' . $post['transactionId'];
		}

		return [
			'message' => 'Transaction completed successfully',
			'transactionID' => $body['output_ConversationID'],
			'returnUrl' => isset($post['returnUrl']) ? $post['returnUrl'] : '',
			'callbackUrl' => isset($post['callbackUrl']) ? $post['callbackUrl'] : '',
		];
	}

	public function getBillingData()
	{
		$cartData = StoreData::getCartData();

		$resilt = [];
		if (isset($cartData->billingInfo->phone)) {
			$resilt['phone'] = $cartData->billingInfo->phone;
		}

		if (isset($cartData->billingInfo->countryCode) && isset(self::COUNTRY_SHORT_TO_NORMAL[$cartData->billingInfo->countryCode])) {
			$resilt['country'] = self::COUNTRY_SHORT_TO_NORMAL[$cartData->billingInfo->countryCode];
		}

		return $resilt;
	}

}