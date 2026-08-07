<?php

class StoreCountry extends StoreRegion {
	/** @var string */
	public $isoCode3 = null;
	/** @var StoreRegion[] */
	public $regions = array();

	public function setISOCode3($code) {
		$this->isoCode3 = $code;
		return $this;
	}

	/**
	 * @param StoreRegion $region
	 * @return StoreCountry
	 */
	public function addRegion(StoreRegion $region) {
		$this->regions[] = $region;
		return $this;
	}
	
	/**
	 * @param string $code country code.
	 * @return StoreCountry
	 */
	public static function findByCode($code) {
		if ($code) foreach (self::buildList() as $li) {
			if ($li->code == $code) return $li;
		}
		return null;
	}

	/**
	 * @param string $isoCode3 country code.
	 * @return StoreCountry
	 */
	public static function findByIsoCode3($isoCode3) {
		if ($isoCode3) foreach (self::buildList() as $li) {
			if ($li->isoCode3 == $isoCode3) return $li;
		}
		return null;
	}


	/**
	 * @param string $countryCode country code.
	 * @param string $regionName region name.
	 * @return array
	 */
	public static function findCountryAndRegion($countryCode, $regionName) {
		$country = $region = null;
		if ($countryCode) {
			foreach (self::buildList() as $li) {
				if ($li->code == $countryCode) {
					$country = $li;
					if( $regionName ) {
						foreach( $country->regions as $r ) {
							if( $r->name == $regionName ) {
								$region = $r;
								break;
							}
						}
					}
					break;
				}
			}
		}
		return array($country, $region);
	}

	/** @return StoreCountry[] */
	public static function buildList() {
		$list = array(
			StoreCountry::create('AF', tr_(array('en' => 'Afghanistan', 'sw' => 'Afghanistan')))->setISOCode3('AFG'),
			StoreCountry::create('AX', tr_(array('en' => 'Åland Islands', 'sw' => 'Visiwa vya Åland')))->setISOCode3('ALA'),
			StoreCountry::create('AL', tr_(array('en' => 'Albania', 'sw' => 'Albania')))->setISOCode3('ALB'),
			StoreCountry::create('DZ', tr_(array('en' => 'Algeria', 'sw' => 'Algeria')))->setISOCode3('DZA'),
			StoreCountry::create('AS', tr_(array('en' => 'American Samoa', 'sw' => 'Samoa ya Marekani')))->setISOCode3('ASM'),
			StoreCountry::create('AD', tr_(array('en' => 'Andorra', 'sw' => 'Andora')))->setISOCode3('AND'),
			StoreCountry::create('AO', tr_(array('en' => 'Angola', 'sw' => 'Angola')))->setISOCode3('AGO'),
			StoreCountry::create('AI', tr_(array('en' => 'Anguilla', 'sw' => 'Anguilla')))->setISOCode3('AIA'),
			StoreCountry::create('AQ', tr_(array('en' => 'Antarctica', 'sw' => 'Antaktika')))->setISOCode3('ATA'),
			StoreCountry::create('AG', tr_(array('en' => 'Antigua & Barbuda', 'sw' => 'Antigua & Barbuda')))->setISOCode3('ATG'),
			StoreCountry::create('AR', tr_(array('en' => 'Argentina', 'sw' => 'Ajentina')))->setISOCode3('ARG'),
			StoreCountry::create('AM', tr_(array('en' => 'Armenia', 'sw' => 'Armenia')))->setISOCode3('ARM'),
			StoreCountry::create('AW', tr_(array('en' => 'Aruba', 'sw' => 'Aruba')))->setISOCode3('ABW'),
			StoreCountry::create('AU', tr_(array('en' => 'Australia', 'sw' => 'Australia')))->setISOCode3('AUS'),
			StoreCountry::create('AT', tr_(array('en' => 'Austria', 'sw' => 'Austria')))->setISOCode3('AUT'),
			StoreCountry::create('AZ', tr_(array('en' => 'Azerbaijan', 'sw' => 'Azabajani')))->setISOCode3('AZE'),
			StoreCountry::create('BS', tr_(array('en' => 'Bahamas', 'sw' => 'Bahamas')))->setISOCode3('BHS'),
			StoreCountry::create('BH', tr_(array('en' => 'Bahrain', 'sw' => 'Bahrain')))->setISOCode3('BHR'),
			StoreCountry::create('BD', tr_(array('en' => 'Bangladesh', 'sw' => 'Bangladesh')))->setISOCode3('BGD'),
			StoreCountry::create('BB', tr_(array('en' => 'Barbados', 'sw' => 'Barbados')))->setISOCode3('BRB'),
			StoreCountry::create('BY', tr_(array('en' => 'Belarus', 'sw' => 'Belarusi')))->setISOCode3('BLR')
				->addRegion(new StoreRegion('HM', tr_(array('en' => 'City of Minsk', 'sw' => 'Mji wa Minsk'))))
				->addRegion(new StoreRegion('BR', tr_(array('en' => 'Brest Region', 'sw' => 'Mkoa wa Brest'))))
				->addRegion(new StoreRegion('HO', tr_(array('en' => 'Gomel Region', 'sw' => 'Mkoa wa Gomel'))))
				->addRegion(new StoreRegion('HR', tr_(array('en' => 'Grodno Region', 'sw' => 'Mkoa wa Grodno'))))
				->addRegion(new StoreRegion('MA', tr_(array('en' => 'Mogilev Region', 'sw' => 'Mkoa wa Mogilev'))))
				->addRegion(new StoreRegion('MI', tr_(array('en' => 'Minsk Region', 'sw' => 'Mkoa wa Minsk'))))
				->addRegion(new StoreRegion('VI', tr_(array('en' => 'Vitebsk Region', 'sw' => 'Mkoa wa Vitebsk')))),
			StoreCountry::create('BE', tr_(array('en' => 'Belgium', 'sw' => 'Ubelgiji')))->setISOCode3('BEL'),
			StoreCountry::create('BZ', tr_(array('en' => 'Belize', 'sw' => 'Belize')))->setISOCode3('BLZ'),
			StoreCountry::create('BJ', tr_(array('en' => 'Benin', 'sw' => 'Benin')))->setISOCode3('BEN'),
			StoreCountry::create('BM', tr_(array('en' => 'Bermuda', 'sw' => 'Bermuda')))->setISOCode3('BMU'),
			StoreCountry::create('BT', tr_(array('en' => 'Bhutan', 'sw' => 'Bhutan')))->setISOCode3('BTN'),
			StoreCountry::create('BO', tr_(array('en' => 'Bolivia', 'sw' => 'Bolivia')))->setISOCode3('BOL'),
			StoreCountry::create('BA', tr_(array('en' => 'Bosnia & Herzegovina', 'sw' => 'Bosnia na Herzegovina')))->setISOCode3('BIH'),
			StoreCountry::create('BW', tr_(array('en' => 'Botswana', 'sw' => 'Botswana')))->setISOCode3('BWA'),
			StoreCountry::create('BV', tr_(array('en' => 'Bouvet Island', 'sw' => 'Kisiwa cha Bouvet')))->setISOCode3('BVT'),
			StoreCountry::create('BR', tr_(array('en' => 'Brazil', 'sw' => 'Brazil')))->setISOCode3('BRA'),
			StoreCountry::create('BQ', tr_(array('en' => 'Caribbean Netherlands', 'sw' => 'Uholanzi wa Karibiani')))->setISOCode3('BES'),
			StoreCountry::create('IO', tr_(array('en' => 'British Indian Ocean Territory', 'sw' => 'Wilaya ya Bahari ya Hindi ya Uingereza')))->setISOCode3('IOT'),
			StoreCountry::create('VG', tr_(array('en' => 'British Virgin Islands', 'sw' => 'Visiwa vya Virgin vya Uingereza')))->setISOCode3('VGB'),
			StoreCountry::create('BN', tr_(array('en' => 'Brunei', 'sw' => 'Brunei')))->setISOCode3('BRN'),
			StoreCountry::create('BG', tr_(array('en' => 'Bulgaria', 'sw' => 'Bulgaria')))->setISOCode3('BGR'),
			StoreCountry::create('BF', tr_(array('en' => 'Burkina Faso', 'sw' => 'Burkina Faso')))->setISOCode3('BFA'),
			StoreCountry::create('BI', tr_(array('en' => 'Burundi', 'sw' => 'Burundi')))->setISOCode3('BDI'),
			StoreCountry::create('KH', tr_(array('en' => 'Cambodia', 'sw' => 'Kambodia')))->setISOCode3('KHM'),
			StoreCountry::create('CM', tr_(array('en' => 'Cameroon', 'sw' => 'Kamerun')))->setISOCode3('CMR'),
			StoreCountry::create('CA', tr_(array('en' => 'Canada', 'sw' => 'Kanada')))->setISOCode3('CAN')
				->addRegion(new StoreRegion('ON', tr_(array('en' => 'Ontario', 'sw' => 'Ontario'))))
				->addRegion(new StoreRegion('QC', tr_(array('en' => 'Quebec', 'sw' => 'Quebec'))))
				->addRegion(new StoreRegion('NS', tr_(array('en' => 'Nova Scotia', 'sw' => 'Nova Scotia'))))
				->addRegion(new StoreRegion('NB', tr_(array('en' => 'New Brunswick', 'sw' => 'Brunswick Mpya'))))
				->addRegion(new StoreRegion('MB', tr_(array('en' => 'Manitoba', 'sw' => 'Manitoba'))))
				->addRegion(new StoreRegion('BC', tr_(array('en' => 'British Columbia', 'sw' => 'British Columbia'))))
				->addRegion(new StoreRegion('PE', tr_(array('en' => 'Prince Edward Island', 'sw' => 'Kisiwa cha Prince Edward'))))
				->addRegion(new StoreRegion('SK', tr_(array('en' => 'Saskatchewan', 'sw' => 'Saskatchewan'))))
				->addRegion(new StoreRegion('AB', tr_(array('en' => 'Alberta', 'sw' => 'Alberta'))))
				->addRegion(new StoreRegion('NL', tr_(array('en' => 'Newfoundland and Labrador', 'sw' => 'Newfoundland na Labrador'))))
				->addRegion(new StoreRegion('NT', tr_(array('en' => 'Northwest Territories', 'sw' => 'Wilaya za Kaskazini Magharibi'))))
				->addRegion(new StoreRegion('YT', tr_(array('en' => 'Yukon', 'sw' => 'Yukon'))))
				->addRegion(new StoreRegion('NU', tr_(array('en' => 'Nunavut', 'sw' => 'Nunavut')))),
			StoreCountry::create('CV', tr_(array('en' => 'Cabo Verde', 'sw' => 'Kabo Verde')))->setISOCode3('CPV'),
			StoreCountry::create('KY', tr_(array('en' => 'Cayman Islands', 'sw' => 'Visiwa vya Cayman')))->setISOCode3('CYM'),
			StoreCountry::create('CF', tr_(array('en' => 'Central African Republic', 'sw' => 'Jamhuri ya Afrika ya Kati')))->setISOCode3('CAF'),
			StoreCountry::create('TD', tr_(array('en' => 'Chad', 'sw' => 'Chad')))->setISOCode3('TCD'),
			StoreCountry::create('CL', tr_(array('en' => 'Chile', 'sw' => 'Chile')))->setISOCode3('CHL'),
			StoreCountry::create('CN', tr_(array('en' => 'China', 'sw' => 'Uchina')))->setISOCode3('CHN'),
			StoreCountry::create('CX', tr_(array('en' => 'Christmas Island', 'sw' => 'Kisiwa cha Krismasi')))->setISOCode3('CXR'),
			StoreCountry::create('CO', tr_(array('en' => 'Colombia', 'sw' => 'Kolombia')))->setISOCode3('COL'),
			StoreCountry::create('KM', tr_(array('en' => 'Comoros', 'sw' => 'Comoro')))->setISOCode3('COM'),
			StoreCountry::create('CG', tr_(array('en' => 'Congo - Brazzaville', 'sw' => 'Kongo - Brazzaville')))->setISOCode3('COG'),
			StoreCountry::create('CD', tr_(array('en' => 'Congo - Kinshasa', 'sw' => 'Kongo - Kinshasa')))->setISOCode3('COD'),
			StoreCountry::create('CK', tr_(array('en' => 'Cook Islands', 'sw' => 'Visiwa vya Cook')))->setISOCode3('COK'),
			StoreCountry::create('CR', tr_(array('en' => 'Costa Rica', 'sw' => 'Kosta Rika')))->setISOCode3('CRI'),
			StoreCountry::create('CI', tr_(array('en' => 'Côte d’Ivoire', 'sw' => 'Cote d\'Ivoire')))->setISOCode3('CIV'),
			StoreCountry::create('HR', tr_(array('en' => 'Croatia', 'sw' => 'Kroatia')))->setISOCode3('HRV'),
			StoreCountry::create('CU', tr_(array('en' => 'Cuba', 'sw' => 'Kuba')))->setISOCode3('CUB'),
			StoreCountry::create('CY', tr_(array('en' => 'Cyprus', 'sw' => 'Kupro')))->setISOCode3('CYP'),
			StoreCountry::create('CZ', tr_(array('en' => 'Czech Republic', 'sw' => 'Jamhuri ya Czech')))->setISOCode3('CZE'),
			StoreCountry::create('DK', tr_(array('en' => 'Denmark', 'sw' => 'Denmark')))->setISOCode3('DNK'),
			StoreCountry::create('DJ', tr_(array('en' => 'Djibouti', 'sw' => 'Djibouti')))->setISOCode3('DJI'),
			StoreCountry::create('DM', tr_(array('en' => 'Dominica', 'sw' => 'Dominika')))->setISOCode3('DMA'),
			StoreCountry::create('DO', tr_(array('en' => 'Dominican Republic', 'sw' => 'Jamhuri ya Dominika')))->setISOCode3('DOM'),
			StoreCountry::create('EC', tr_(array('en' => 'Ecuador', 'sw' => 'Ekvado')))->setISOCode3('ECU'),
			StoreCountry::create('EG', tr_(array('en' => 'Egypt', 'sw' => 'Misri')))->setISOCode3('EGY'),
			StoreCountry::create('SV', tr_(array('en' => 'El Salvador', 'sw' => 'El Salvador')))->setISOCode3('SLV'),
			StoreCountry::create('GQ', tr_(array('en' => 'Equatorial Guinea', 'sw' => 'Guinea ya Ikweta')))->setISOCode3('GNQ'),
			StoreCountry::create('ER', tr_(array('en' => 'Eritrea', 'sw' => 'Eritrea')))->setISOCode3('ERI'),
			StoreCountry::create('EE', tr_(array('en' => 'Estonia', 'sw' => 'Estonia')))->setISOCode3('EST'),
			StoreCountry::create('ET', tr_(array('en' => 'Ethiopia', 'sw' => 'Ethiopia')))->setISOCode3('ETH'),
			StoreCountry::create('FK', tr_(array('en' => 'Falkland Islands', 'sw' => 'Visiwa vya Falkland')))->setISOCode3('FLK'),
			StoreCountry::create('FO', tr_(array('en' => 'Faroe Islands', 'sw' => 'Visiwa vya Faroe')))->setISOCode3('FRO'),
			StoreCountry::create('FJ', tr_(array('en' => 'Fiji', 'sw' => 'Fiji')))->setISOCode3('FJI'),
			StoreCountry::create('FI', tr_(array('en' => 'Finland', 'sw' => 'Ufini')))->setISOCode3('FIN'),
			StoreCountry::create('FR', tr_(array('en' => 'France', 'sw' => 'Ufaransa')))->setISOCode3('FRA'),
			StoreCountry::create('GF', tr_(array('en' => 'French Guiana', 'sw' => 'Guiana ya Ufaransa')))->setISOCode3('GUF'),
			StoreCountry::create('PF', tr_(array('en' => 'French Polynesia', 'sw' => 'Polynesia ya Ufaransa')))->setISOCode3('PYF'),
			StoreCountry::create('TF', tr_(array('en' => 'French Southern Territories', 'sw' => 'Maeneo ya Kusini mwa Ufaransa')))->setISOCode3('ATF'),
			StoreCountry::create('GA', tr_(array('en' => 'Gabon', 'sw' => 'Gabon')))->setISOCode3('GAB'),
			StoreCountry::create('GM', tr_(array('en' => 'Gambia', 'sw' => 'Gambia')))->setISOCode3('GMB'),
			StoreCountry::create('GE', tr_(array('en' => 'Georgia', 'sw' => 'Georgia')))->setISOCode3('GEO'),
			StoreCountry::create('DE', tr_(array('en' => 'Germany', 'sw' => 'Ujerumani')))->setISOCode3('DEU'),
			StoreCountry::create('GH', tr_(array('en' => 'Ghana', 'sw' => 'Ghana')))->setISOCode3('GHA'),
			StoreCountry::create('GI', tr_(array('en' => 'Gibraltar', 'sw' => 'Gibraltar')))->setISOCode3('GIB'),
			StoreCountry::create('GR', tr_(array('en' => 'Greece', 'sw' => 'Ugiriki')))->setISOCode3('GRC'),
			StoreCountry::create('GL', tr_(array('en' => 'Greenland', 'sw' => 'Greenland')))->setISOCode3('GRL'),
			StoreCountry::create('GD', tr_(array('en' => 'Grenada', 'sw' => 'Grenada')))->setISOCode3('GRD'),
			StoreCountry::create('GP', tr_(array('en' => 'Guadeloupe', 'sw' => 'Guadeloupe')))->setISOCode3('GLP'),
			StoreCountry::create('GU', tr_(array('en' => 'Guam', 'sw' => 'Guam')))->setISOCode3('GUM'),
			StoreCountry::create('GT', tr_(array('en' => 'Guatemala', 'sw' => 'Guatemala')))->setISOCode3('GTM'),
			StoreCountry::create('GG', tr_(array('en' => 'Guernsey', 'sw' => 'Guernsey')))->setISOCode3('GGY'),
			StoreCountry::create('GN', tr_(array('en' => 'Guinea', 'sw' => 'Gine')))->setISOCode3('GIN'),
			StoreCountry::create('GW', tr_(array('en' => 'Guinea-Bissau', 'sw' => 'Guinea-Bissau')))->setISOCode3('GNB'),
			StoreCountry::create('GY', tr_(array('en' => 'Guyana', 'sw' => 'Guyana')))->setISOCode3('GUY'),
			StoreCountry::create('HT', tr_(array('en' => 'Haiti', 'sw' => 'Haiti')))->setISOCode3('HTI'),
			StoreCountry::create('HM', tr_(array('en' => 'Heard Island and McDonald Islands', 'sw' => 'Kisiwa cha Heard na Visiwa vya McDonald')))->setISOCode3('HMD'),
			StoreCountry::create('HN', tr_(array('en' => 'Honduras', 'sw' => 'Honduras')))->setISOCode3('HND'),
			StoreCountry::create('HK', tr_(array('en' => 'Hong Kong SAR China', 'sw' => 'Hong Kong SAR Uchina')))->setISOCode3('HKG'),
			StoreCountry::create('HU', tr_(array('en' => 'Hungary', 'sw' => 'Hungaria')))->setISOCode3('HUN'),
			StoreCountry::create('IS', tr_(array('en' => 'Iceland', 'sw' => 'Iceland')))->setISOCode3('ISL'),
			StoreCountry::create('IN', tr_(array('en' => 'India', 'sw' => 'Uhindi')))->setISOCode3('IND'),
			StoreCountry::create('ID', tr_(array('en' => 'Indonesia', 'sw' => 'Indonesia')))->setISOCode3('IDN'),
			StoreCountry::create('IR', tr_(array('en' => 'Iran', 'sw' => 'Irani')))->setISOCode3('IRN'),
			StoreCountry::create('IQ', tr_(array('en' => 'Iraq', 'sw' => 'Iraq')))->setISOCode3('IRQ'),
			StoreCountry::create('IE', tr_(array('en' => 'Ireland', 'sw' => 'Ireland')))->setISOCode3('IRL'),
			StoreCountry::create('IM', tr_(array('en' => 'Isle of Man', 'sw' => 'Kisiwa cha Mtu')))->setISOCode3('IMN'),
			StoreCountry::create('IL', tr_(array('en' => 'Israel', 'sw' => 'Israeli')))->setISOCode3('ISR'),
			StoreCountry::create('IT', tr_(array('en' => 'Italy', 'sw' => 'Italia')))->setISOCode3('ITA'),
			StoreCountry::create('JM', tr_(array('en' => 'Jamaica', 'sw' => 'Jamaika')))->setISOCode3('JAM'),
			StoreCountry::create('JP', tr_(array('en' => 'Japan', 'sw' => 'Japani')))->setISOCode3('JPN'),
			StoreCountry::create('JE', tr_(array('en' => 'Jersey', 'sw' => 'Jezi')))->setISOCode3('JEY'),
			StoreCountry::create('JO', tr_(array('en' => 'Jordan', 'sw' => 'Yordani')))->setISOCode3('JOR'),
			StoreCountry::create('KZ', tr_(array('en' => 'Kazakhstan', 'sw' => 'Kazakhstan')))->setISOCode3('KAZ')
				->addRegion(new StoreRegion('ABA', tr_(array('en' => 'Abai Region', 'sw' => 'Mkoa wa Abai'))))
				->addRegion(new StoreRegion('AKM', tr_(array('en' => 'Akmola Region', 'sw' => 'Mkoa wa Akmola'))))
				->addRegion(new StoreRegion('AKT', tr_(array('en' => 'Aktobe Region', 'sw' => 'Mkoa wa Aktobe'))))
				->addRegion(new StoreRegion('ALA', tr_(array('en' => 'Almaty', 'sw' => 'Almaty'))))
				->addRegion(new StoreRegion('ALM', tr_(array('en' => 'Almaty Region', 'sw' => 'Mkoa wa Almaty'))))
				->addRegion(new StoreRegion('ATY', tr_(array('en' => 'Atyrau Region', 'sw' => 'Mkoa wa Atyrau'))))
				->addRegion(new StoreRegion('BAY', tr_(array('en' => 'Baikonur', 'sw' => 'Baikonur'))))
				->addRegion(new StoreRegion('VOS', tr_(array('en' => 'East Kazakhstan Region', 'sw' => 'Mkoa wa Kazakhstan Mashariki'))))
				->addRegion(new StoreRegion('ZHA', tr_(array('en' => 'Jambyl Region', 'sw' => 'Mkoa wa Jaybyl'))))
				->addRegion(new StoreRegion('JET', tr_(array('en' => 'Jetisu Region', 'sw' => 'Mkoa wa Jetisu'))))
				->addRegion(new StoreRegion('KAR', tr_(array('en' => 'Karaganda Region', 'sw' => 'Mkoa wa Karaganda'))))
				->addRegion(new StoreRegion('KUS', tr_(array('en' => 'Kostanay Region', 'sw' => 'Mkoa wa Kostanay'))))
				->addRegion(new StoreRegion('KZY', tr_(array('en' => 'Kyzylorda Region', 'sw' => 'Mkoa wa Kyzylorda'))))
				->addRegion(new StoreRegion('MAN', tr_(array('en' => 'Mangystau Region', 'sw' => 'Mkoa wa Mangystau'))))
				->addRegion(new StoreRegion('SEV', tr_(array('en' => 'North Kazakhstan Region', 'sw' => 'Mkoa wa Kaskazini wa Kazakhstan'))))
				->addRegion(new StoreRegion('AST', tr_(array('en' => 'Nur-Sultan', 'sw' => 'Nur-Sultan'))))
				->addRegion(new StoreRegion('PAV', tr_(array('en' => 'Pavlodar Region', 'sw' => 'Mkoa wa Pavlodar'))))
				->addRegion(new StoreRegion('SHY', tr_(array('en' => 'Shymkent', 'sw' => 'Shymkent'))))
				->addRegion(new StoreRegion('YUZ', tr_(array('en' => 'Turkistan Region', 'sw' => 'Mkoa wa Turkistan'))))
				->addRegion(new StoreRegion('ULY', tr_(array('en' => 'Ulytau Region', 'sw' => 'Mkoa wa Ulytau'))))
				->addRegion(new StoreRegion('ZAP', tr_(array('en' => 'West Kazakhstan Region', 'sw' => 'Mkoa wa Kazakhstan Magharibi')))),
			StoreCountry::create('KE', tr_(array('en' => 'Kenya', 'sw' => 'Kenya')))->setISOCode3('KEN'),
			StoreCountry::create('KI', tr_(array('en' => 'Kiribati', 'sw' => 'Kiribati')))->setISOCode3('KIR'),
			StoreCountry::create('KW', tr_(array('en' => 'Kuwait', 'sw' => 'Kuwait')))->setISOCode3('KWT'),
			StoreCountry::create('KG', tr_(array('en' => 'Kyrgyzstan', 'sw' => 'Kyrgyzstan')))->setISOCode3('KGZ'),
			StoreCountry::create('LA', tr_(array('en' => 'Laos', 'sw' => 'Laos')))->setISOCode3('LAO'),
			StoreCountry::create('LV', tr_(array('en' => 'Latvia', 'sw' => 'Latvia')))->setISOCode3('LVA'),
			StoreCountry::create('LB', tr_(array('en' => 'Lebanon', 'sw' => 'Lebanon')))->setISOCode3('LBN'),
			StoreCountry::create('LS', tr_(array('en' => 'Lesotho', 'sw' => 'Lesotho')))->setISOCode3('LSO'),
			StoreCountry::create('LR', tr_(array('en' => 'Liberia', 'sw' => 'Liberia')))->setISOCode3('LBR'),
			StoreCountry::create('LY', tr_(array('en' => 'Libya', 'sw' => 'Libya')))->setISOCode3('LBY'),
			StoreCountry::create('LI', tr_(array('en' => 'Liechtenstein', 'sw' => 'Liechtenstein')))->setISOCode3('LIE'),
			StoreCountry::create('LT', tr_(array('en' => 'Lithuania', 'sw' => 'Lithuania')))->setISOCode3('LTU'),
			StoreCountry::create('LU', tr_(array('en' => 'Luxembourg', 'sw' => 'Luxemburg')))->setISOCode3('LUX'),
			StoreCountry::create('MO', tr_(array('en' => 'Macau SAR China', 'sw' => 'Macau SAR Uchina')))->setISOCode3('MAC'),
			StoreCountry::create('MK', tr_(array('en' => 'North Macedonia', 'sw' => 'Makedonia Kaskazini')))->setISOCode3('MKD'),
			StoreCountry::create('MG', tr_(array('en' => 'Madagascar', 'sw' => 'Madagaska')))->setISOCode3('MDG'),
			StoreCountry::create('MW', tr_(array('en' => 'Malawi', 'sw' => 'Malawi')))->setISOCode3('MWI'),
			StoreCountry::create('MY', tr_(array('en' => 'Malaysia', 'sw' => 'Malaysia')))->setISOCode3('MYS'),
			StoreCountry::create('MV', tr_(array('en' => 'Maldives', 'sw' => 'Maldives')))->setISOCode3('MDV'),
			StoreCountry::create('ML', tr_(array('en' => 'Mali', 'sw' => 'Mali')))->setISOCode3('MLI'),
			StoreCountry::create('MT', tr_(array('en' => 'Malta', 'sw' => 'Malta')))->setISOCode3('MLT'),
			StoreCountry::create('MH', tr_(array('en' => 'Marshall Islands', 'sw' => 'Visiwa vya Marshall')))->setISOCode3('MHL'),
			StoreCountry::create('MQ', tr_(array('en' => 'Martinique', 'sw' => 'Martinique')))->setISOCode3('MTQ'),
			StoreCountry::create('MR', tr_(array('en' => 'Mauritania', 'sw' => 'Mauritania')))->setISOCode3('MRT'),
			StoreCountry::create('MU', tr_(array('en' => 'Mauritius', 'sw' => 'Morisi')))->setISOCode3('MUS'),
			StoreCountry::create('YT', tr_(array('en' => 'Mayotte', 'sw' => 'Mayotte')))->setISOCode3('MYT'),
			StoreCountry::create('MX', tr_(array('en' => 'Mexico', 'sw' => 'Mexico')))->setISOCode3('MEX'),
			StoreCountry::create('FM', tr_(array('en' => 'Micronesia', 'sw' => 'Mikronesia')))->setISOCode3('FSM'),
			StoreCountry::create('MD', tr_(array('en' => 'Moldova', 'sw' => 'Moldova')))->setISOCode3('MDA'),
			StoreCountry::create('MC', tr_(array('en' => 'Monaco', 'sw' => 'Monako')))->setISOCode3('MCO'),
			StoreCountry::create('MN', tr_(array('en' => 'Mongolia', 'sw' => 'Mongolia')))->setISOCode3('MNG'),
			StoreCountry::create('ME', tr_(array('en' => 'Montenegro', 'sw' => 'Montenegro')))->setISOCode3('MNE'),
			StoreCountry::create('MS', tr_(array('en' => 'Montserrat', 'sw' => 'Montserrat')))->setISOCode3('MSR'),
			StoreCountry::create('MA', tr_(array('en' => 'Morocco', 'sw' => 'Moroko')))->setISOCode3('MAR'),
			StoreCountry::create('MZ', tr_(array('en' => 'Mozambique', 'sw' => 'Msumbiji')))->setISOCode3('MOZ'),
			StoreCountry::create('MM', tr_(array('en' => 'Myanmar (Burma)', 'sw' => 'Myanmar [Burma]')))->setISOCode3('MMR'),
			StoreCountry::create('NA', tr_(array('en' => 'Namibia', 'sw' => 'Namibia')))->setISOCode3('NAM'),
			StoreCountry::create('NR', tr_(array('en' => 'Nauru', 'sw' => 'Nauru')))->setISOCode3('NRU'),
			StoreCountry::create('NP', tr_(array('en' => 'Nepal', 'sw' => 'Nepal')))->setISOCode3('NPL'),
			StoreCountry::create('NL', tr_(array('en' => 'Netherlands', 'sw' => 'Uholanzi')))->setISOCode3('NLD'),
			StoreCountry::create('NC', tr_(array('en' => 'New Caledonia', 'sw' => 'Kaledonia mpya')))->setISOCode3('NCL'),
			StoreCountry::create('NZ', tr_(array('en' => 'New Zealand', 'sw' => 'New Zealand')))->setISOCode3('NZL'),
			StoreCountry::create('NI', tr_(array('en' => 'Nicaragua', 'sw' => 'Nikaragua')))->setISOCode3('NIC'),
			StoreCountry::create('NE', tr_(array('en' => 'Niger', 'sw' => 'Niger')))->setISOCode3('NER'),
			StoreCountry::create('NG', tr_(array('en' => 'Nigeria', 'sw' => 'Nigeria')))->setISOCode3('NGA')
				->addRegion(new StoreRegion('AB', tr_(array('en' => 'Abia', 'sw' => 'Abia'))))
				->addRegion(new StoreRegion('AD', tr_(array('en' => 'Adamawa', 'sw' => 'Adamawa'))))
				->addRegion(new StoreRegion('AK', tr_(array('en' => 'Akwa Ibom', 'sw' => 'Akwa Ibom'))))
				->addRegion(new StoreRegion('AN', tr_(array('en' => 'Anambra', 'sw' => 'Anambra'))))
				->addRegion(new StoreRegion('BA', tr_(array('en' => 'Bauchi', 'sw' => 'Bauchi'))))
				->addRegion(new StoreRegion('BY', tr_(array('en' => 'Bayelsa', 'sw' => 'Bayelsa'))))
				->addRegion(new StoreRegion('BE', tr_(array('en' => 'Benue', 'sw' => 'Benue'))))
				->addRegion(new StoreRegion('BO', tr_(array('en' => 'Borno', 'sw' => 'Borno'))))
				->addRegion(new StoreRegion('CR', tr_(array('en' => 'Cross River', 'sw' => 'Cross River'))))
				->addRegion(new StoreRegion('DE', tr_(array('en' => 'Delta', 'sw' => 'Delta'))))
				->addRegion(new StoreRegion('EB', tr_(array('en' => 'Ebonyi', 'sw' => 'Ebonyi'))))
				->addRegion(new StoreRegion('ED', tr_(array('en' => 'Edo', 'sw' => 'Edo'))))
				->addRegion(new StoreRegion('EK', tr_(array('en' => 'Ekiti', 'sw' => 'Ekiti'))))
				->addRegion(new StoreRegion('EN', tr_(array('en' => 'Enugu', 'sw' => 'Enugu'))))
				->addRegion(new StoreRegion('GO', tr_(array('en' => 'Gombe', 'sw' => 'Gombe'))))
				->addRegion(new StoreRegion('IM', tr_(array('en' => 'Imo', 'sw' => 'Imo'))))
				->addRegion(new StoreRegion('JI', tr_(array('en' => 'Jigawa', 'sw' => 'Jigawa'))))
				->addRegion(new StoreRegion('KD', tr_(array('en' => 'Kaduna', 'sw' => 'Kaduna'))))
				->addRegion(new StoreRegion('KN', tr_(array('en' => 'Kano', 'sw' => 'Kano'))))
				->addRegion(new StoreRegion('KT', tr_(array('en' => 'Katsina', 'sw' => 'Katsina'))))
				->addRegion(new StoreRegion('KE', tr_(array('en' => 'Kebbi', 'sw' => 'Kebbi'))))
				->addRegion(new StoreRegion('KO', tr_(array('en' => 'Kogi', 'sw' => 'Kogi'))))
				->addRegion(new StoreRegion('KW', tr_(array('en' => 'Kwara', 'sw' => 'Kwara'))))
				->addRegion(new StoreRegion('LA', tr_(array('en' => 'Lagos', 'sw' => 'Lagos'))))
				->addRegion(new StoreRegion('NA', tr_(array('en' => 'Nasarawa', 'sw' => 'Nasarawa'))))
				->addRegion(new StoreRegion('NI', tr_(array('en' => 'Niger', 'sw' => 'Niger'))))
				->addRegion(new StoreRegion('OG', tr_(array('en' => 'Ogun', 'sw' => 'Ogun'))))
				->addRegion(new StoreRegion('ON', tr_(array('en' => 'Ondo', 'sw' => 'Ondo'))))
				->addRegion(new StoreRegion('OS', tr_(array('en' => 'Osun', 'sw' => 'Osun'))))
				->addRegion(new StoreRegion('OY', tr_(array('en' => 'Oyo', 'sw' => 'Oyo'))))
				->addRegion(new StoreRegion('PL', tr_(array('en' => 'Plateau', 'sw' => 'Plateau'))))
				->addRegion(new StoreRegion('RI', tr_(array('en' => 'Rivers', 'sw' => 'Rivers'))))
				->addRegion(new StoreRegion('SO', tr_(array('en' => 'Sokoto', 'sw' => 'Sokoto'))))
				->addRegion(new StoreRegion('TA', tr_(array('en' => 'Taraba', 'sw' => 'Taraba'))))
				->addRegion(new StoreRegion('YO', tr_(array('en' => 'Yobe', 'sw' => 'Yobe'))))
				->addRegion(new StoreRegion('ZA', tr_(array('en' => 'Zamfara', 'sw' => 'Zamfara')))),
			StoreCountry::create('NU', tr_(array('en' => 'Niue', 'sw' => 'Niue')))->setISOCode3('NIU'),
			StoreCountry::create('NF', tr_(array('en' => 'Norfolk Island', 'sw' => 'Kisiwa cha Norfolk')))->setISOCode3('NFK'),
			StoreCountry::create('KP', tr_(array('en' => 'North Korea', 'sw' => 'Korea Kaskazini')))->setISOCode3('PRK'),
			StoreCountry::create('MP', tr_(array('en' => 'Northern Mariana Islands', 'sw' => 'Visiwa vya Mariana Kaskazini')))->setISOCode3('MNP'),
			StoreCountry::create('NO', tr_(array('en' => 'Norway', 'sw' => 'Norway')))->setISOCode3('NOR'),
			StoreCountry::create('OM', tr_(array('en' => 'Oman', 'sw' => 'Oman')))->setISOCode3('OMN'),
			StoreCountry::create('PK', tr_(array('en' => 'Pakistan', 'sw' => 'Pakistani')))->setISOCode3('PAK'),
			StoreCountry::create('PW', tr_(array('en' => 'Palau', 'sw' => 'Palau')))->setISOCode3('PLW'),
			StoreCountry::create('PS', tr_(array('en' => 'Palestinian Territories', 'sw' => 'Palestinian Territories')))->setISOCode3('PSE'),
			StoreCountry::create('PA', tr_(array('en' => 'Panama', 'sw' => 'Panama')))->setISOCode3('PAN'),
			StoreCountry::create('PG', tr_(array('en' => 'Papua New Guinea', 'sw' => 'Papua Guinea Mpya')))->setISOCode3('PNG'),
			StoreCountry::create('PY', tr_(array('en' => 'Paraguay', 'sw' => 'Paragwai')))->setISOCode3('PRY'),
			StoreCountry::create('PE', tr_(array('en' => 'Peru', 'sw' => 'Peru')))->setISOCode3('PER'),
			StoreCountry::create('PH', tr_(array('en' => 'Philippines', 'sw' => 'Ufilipino')))->setISOCode3('PHL'),
			StoreCountry::create('PL', tr_(array('en' => 'Poland', 'sw' => 'Poland')))->setISOCode3('POL'),
			StoreCountry::create('PT', tr_(array('en' => 'Portugal', 'sw' => 'Ureno')))->setISOCode3('PRT'),
			StoreCountry::create('PR', tr_(array('en' => 'Puerto Rico', 'sw' => 'Puerto Rico')))->setISOCode3('PRI'),
			StoreCountry::create('QA', tr_(array('en' => 'Qatar', 'sw' => 'Qatar')))->setISOCode3('QAT'),
			StoreCountry::create('RE', tr_(array('en' => 'Réunion', 'sw' => 'Réunion')))->setISOCode3('REU'),
			StoreCountry::create('RO', tr_(array('en' => 'Romania', 'sw' => 'Rumania')))->setISOCode3('ROU'),
			StoreCountry::create('RU', tr_(array('en' => 'Russia', 'sw' => 'Urusi')))->setISOCode3('RUS'),
			StoreCountry::create('RW', tr_(array('en' => 'Rwanda', 'sw' => 'Rwanda')))->setISOCode3('RWA'),
			StoreCountry::create('BL', tr_(array('en' => 'St. Barthélemy', 'sw' => 'Mtakatifu Barthélemy')))->setISOCode3('BLM'),
			StoreCountry::create('SH', tr_(array('en' => 'St. Helena', 'sw' => 'Mtakatifu Helena')))->setISOCode3('SHN'),
			StoreCountry::create('KN', tr_(array('en' => 'St. Kitts & Nevis', 'sw' => 'Kitts & Nevis')))->setISOCode3('KNA'),
			StoreCountry::create('LC', tr_(array('en' => 'St. Lucia', 'sw' => 'Mtakatifu Lucia')))->setISOCode3('LCA'),
			StoreCountry::create('MF', tr_(array('en' => 'St. Martin', 'sw' => 'Mtakatifu Martin')))->setISOCode3('MAF'),
			StoreCountry::create('PM', tr_(array('en' => 'St. Pierre & Miquelon', 'sw' => 'Mtakatifu Pierre & Miquelon')))->setISOCode3('SPM'),
			StoreCountry::create('VC', tr_(array('en' => 'St. Vincent & Grenadines', 'sw' => 'Vincent na Grenadines')))->setISOCode3('VCT'),
			StoreCountry::create('WS', tr_(array('en' => 'Samoa', 'sw' => 'Samoa')))->setISOCode3('WSM'),
			StoreCountry::create('SM', tr_(array('en' => 'San Marino', 'sw' => 'San Marino')))->setISOCode3('SMR'),
			StoreCountry::create('ST', tr_(array('en' => 'São Tomé & Príncipe', 'sw' => 'São Tomé na Príncipe')))->setISOCode3('STP'),
			StoreCountry::create('SA', tr_(array('en' => 'Saudi Arabia', 'sw' => 'Saudi Arabia')))->setISOCode3('SAU'),
			StoreCountry::create('SN', tr_(array('en' => 'Senegal', 'sw' => 'Senegal')))->setISOCode3('SEN'),
			StoreCountry::create('RS', tr_(array('en' => 'Serbia', 'sw' => 'Serbia')))->setISOCode3('SRB'),
			StoreCountry::create('SC', tr_(array('en' => 'Seychelles', 'sw' => 'Shelisheli')))->setISOCode3('SYC'),
			StoreCountry::create('SL', tr_(array('en' => 'Sierra Leone', 'sw' => 'Sierra Leone')))->setISOCode3('SLE'),
			StoreCountry::create('SG', tr_(array('en' => 'Singapore', 'sw' => 'Singapore')))->setISOCode3('SGP'),
			StoreCountry::create('SK', tr_(array('en' => 'Slovakia', 'sw' => 'Slovakia')))->setISOCode3('SVK'),
			StoreCountry::create('SI', tr_(array('en' => 'Slovenia', 'sw' => 'Slovenia')))->setISOCode3('SVN'),
			StoreCountry::create('SB', tr_(array('en' => 'Solomon Islands', 'sw' => 'Visiwa vya Solomon')))->setISOCode3('SLB'),
			StoreCountry::create('SO', tr_(array('en' => 'Somalia', 'sw' => 'Somalia')))->setISOCode3('SOM'),
			StoreCountry::create('ZA', tr_(array('en' => 'South Africa', 'sw' => 'Africa Kusini')))->setISOCode3('ZAF'),
			StoreCountry::create('GS', tr_(array('en' => 'South Georgia & South Sandwich Islands', 'sw' => 'Visiwa vya Georgia Kusini na Sandwich Kusini')))->setISOCode3('SGS'),
			StoreCountry::create('KR', tr_(array('en' => 'South Korea', 'sw' => 'Korea Kusini')))->setISOCode3('KOR'),
			StoreCountry::create('ES', tr_(array('en' => 'Spain', 'sw' => 'Uhispania')))->setISOCode3('ESP')
				->addRegion(new StoreRegion('VI', tr_(array('en' => 'Álava', 'sw' => 'Álava'))))
				->addRegion(new StoreRegion('AB', tr_(array('en' => 'Albacete', 'sw' => 'Albacete'))))
				->addRegion(new StoreRegion('A', tr_(array('en' => 'Alicante', 'sw' => 'Alicante'))))
				->addRegion(new StoreRegion('AL', tr_(array('en' => 'Almería', 'sw' => 'Almería'))))
				->addRegion(new StoreRegion('O', tr_(array('en' => 'Asturias', 'sw' => 'Asturias'))))
				->addRegion(new StoreRegion('AV', tr_(array('en' => 'Ávila', 'sw' => 'Ávila'))))
				->addRegion(new StoreRegion('BA', tr_(array('en' => 'Badajoz', 'sw' => 'Badajoz'))))
				->addRegion(new StoreRegion('B', tr_(array('en' => 'Barcelona', 'sw' => 'Barcelona'))))
				->addRegion(new StoreRegion('BU', tr_(array('en' => 'Burgos', 'sw' => 'Burgos'))))
				->addRegion(new StoreRegion('CC', tr_(array('en' => 'Cáceres', 'sw' => 'Cáceres'))))
				->addRegion(new StoreRegion('CA', tr_(array('en' => 'Cádiz', 'sw' => 'Cádiz'))))
				->addRegion(new StoreRegion('S', tr_(array('en' => 'Cantabria', 'sw' => 'Cantabria'))))
				->addRegion(new StoreRegion('CS', tr_(array('en' => 'Castellón de la Plana', 'sw' => 'Castellón de la Plana'))))
				->addRegion(new StoreRegion('CE', tr_(array('en' => 'Ceuta', 'sw' => 'Ceuta'))))
				->addRegion(new StoreRegion('CR', tr_(array('en' => 'Ciudad Real', 'sw' => 'Ciudad Real'))))
				->addRegion(new StoreRegion('CO', tr_(array('en' => 'Córdoba', 'sw' => 'Córdoba'))))
				->addRegion(new StoreRegion('CU', tr_(array('en' => 'Cuenca', 'sw' => 'Cuenca'))))
				->addRegion(new StoreRegion('GI', tr_(array('en' => 'Gerona', 'sw' => 'Gerona'))))
				->addRegion(new StoreRegion('GR', tr_(array('en' => 'Granada', 'sw' => 'Granada'))))
				->addRegion(new StoreRegion('GU', tr_(array('en' => 'Guadalajara', 'sw' => 'Guadalajara'))))
				->addRegion(new StoreRegion('SS', tr_(array('en' => 'Guipúzcoa', 'sw' => 'Guipúzcoa'))))
				->addRegion(new StoreRegion('H', tr_(array('en' => 'Huelva', 'sw' => 'Huelva'))))
				->addRegion(new StoreRegion('HU', tr_(array('en' => 'Huesca', 'sw' => 'Huesca'))))
				->addRegion(new StoreRegion('PM', tr_(array('en' => 'Islas Baleares', 'sw' => 'Islas Baleares'))))
				->addRegion(new StoreRegion('J', tr_(array('en' => 'Jaén', 'sw' => 'Jaén'))))
				->addRegion(new StoreRegion('C', tr_(array('en' => 'La Coruña', 'sw' => 'La Coruña'))))
				->addRegion(new StoreRegion('LO', tr_(array('en' => 'La Rioja', 'sw' => 'La Rioja'))))
				->addRegion(new StoreRegion('GC', tr_(array('en' => 'Las Palmas (Islas Canarias)', 'sw' => 'Las Palmas (Islas Canarias)'))))
				->addRegion(new StoreRegion('LE', tr_(array('en' => 'León', 'sw' => 'León'))))
				->addRegion(new StoreRegion('L', tr_(array('en' => 'Lérida', 'sw' => 'Lérida'))))
				->addRegion(new StoreRegion('LU', tr_(array('en' => 'Lugo', 'sw' => 'Lugo'))))
				->addRegion(new StoreRegion('M', tr_(array('en' => 'Madrid', 'sw' => 'Madrid'))))
				->addRegion(new StoreRegion('MA', tr_(array('en' => 'Málaga', 'sw' => 'Málaga'))))
				->addRegion(new StoreRegion('ML', tr_(array('en' => 'Melilla', 'sw' => 'Melilla'))))
				->addRegion(new StoreRegion('MU', tr_(array('en' => 'Murcia', 'sw' => 'Murcia'))))
				->addRegion(new StoreRegion('NA', tr_(array('en' => 'Navarra', 'sw' => 'Navarra'))))
				->addRegion(new StoreRegion('OR', tr_(array('en' => 'Orense', 'sw' => 'Orense'))))
				->addRegion(new StoreRegion('P', tr_(array('en' => 'Palencia', 'sw' => 'Palencia'))))
				->addRegion(new StoreRegion('PO', tr_(array('en' => 'Pontevedra', 'sw' => 'Pontevedra'))))
				->addRegion(new StoreRegion('SA', tr_(array('en' => 'Salamanca', 'sw' => 'Salamanca'))))
				->addRegion(new StoreRegion('TF', tr_(array('en' => 'Santa Cruz de Tenerife (Islas Canarias)', 'sw' => 'Santa Cruz de Tenerife (Islas Canarias)'))))
				->addRegion(new StoreRegion('SG', tr_(array('en' => 'Segovia', 'sw' => 'Segovia'))))
				->addRegion(new StoreRegion('SE', tr_(array('en' => 'Sevilla', 'sw' => 'Sevilla'))))
				->addRegion(new StoreRegion('SO', tr_(array('en' => 'Soria', 'sw' => 'Soria'))))
				->addRegion(new StoreRegion('T', tr_(array('en' => 'Tarragona', 'sw' => 'Tarragona'))))
				->addRegion(new StoreRegion('TE', tr_(array('en' => 'Teruel', 'sw' => 'Teruel'))))
				->addRegion(new StoreRegion('TO', tr_(array('en' => 'Toledo', 'sw' => 'Toledo'))))
				->addRegion(new StoreRegion('V', tr_(array('en' => 'Valencia', 'sw' => 'Valencia'))))
				->addRegion(new StoreRegion('VA', tr_(array('en' => 'Valladolid', 'sw' => 'Valladolid'))))
				->addRegion(new StoreRegion('BI', tr_(array('en' => 'Vizcaya', 'sw' => 'Vizcaya'))))
				->addRegion(new StoreRegion('ZA', tr_(array('en' => 'Zamora', 'sw' => 'Zamora'))))
				->addRegion(new StoreRegion('Z', tr_(array('en' => 'Zaragoza', 'sw' => 'Zaragoza')))),
			StoreCountry::create('LK', tr_(array('en' => 'Sri Lanka', 'sw' => 'Sri Lanka')))->setISOCode3('LKA'),
			StoreCountry::create('SD', tr_(array('en' => 'Sudan', 'sw' => 'Sudan')))->setISOCode3('SDN'),
			StoreCountry::create('SR', tr_(array('en' => 'Suriname', 'sw' => 'Surinam')))->setISOCode3('SUR'),
			StoreCountry::create('SZ', tr_(array('en' => 'Eswatini (Swaziland)', 'sw' => 'Eswatini (Swaziland)')))->setISOCode3('SWZ'),
			StoreCountry::create('SE', tr_(array('en' => 'Sweden', 'sw' => 'Uswidi')))->setISOCode3('SWE'),
			StoreCountry::create('CH', tr_(array('en' => 'Switzerland', 'sw' => 'Uswizi')))->setISOCode3('CHE'),
			StoreCountry::create('SY', tr_(array('en' => 'Syria', 'sw' => 'Syria')))->setISOCode3('SYR'),
			StoreCountry::create('TW', tr_(array('en' => 'Taiwan', 'sw' => 'Taiwan')))->setISOCode3('TWN'),
			StoreCountry::create('TJ', tr_(array('en' => 'Tajikistan', 'sw' => 'Tajikistan')))->setISOCode3('TJK'),
			StoreCountry::create('TZ', tr_(array('en' => 'Tanzania', 'sw' => 'Tanzania')))->setISOCode3('TZA'),
			StoreCountry::create('TH', tr_(array('en' => 'Thailand', 'sw' => 'Thailand')))->setISOCode3('THA'),
			StoreCountry::create('TL', tr_(array('en' => 'Timor-Leste', 'sw' => 'Timor-Leste')))->setISOCode3('TLS'),
			StoreCountry::create('TG', tr_(array('en' => 'Togo', 'sw' => 'Togo')))->setISOCode3('TGO'),
			StoreCountry::create('TK', tr_(array('en' => 'Tokelau', 'sw' => 'Tokelau')))->setISOCode3('TKL'),
			StoreCountry::create('TO', tr_(array('en' => 'Tonga', 'sw' => 'Tonga')))->setISOCode3('TON'),
			StoreCountry::create('TT', tr_(array('en' => 'Trinidad and Tobago', 'sw' => 'Trinidad na Tobago')))->setISOCode3('TTO'),
			StoreCountry::create('TN', tr_(array('en' => 'Tunisia', 'sw' => 'Tunisia')))->setISOCode3('TUN'),
			StoreCountry::create('TR', tr_(array('en' => 'Turkey', 'sw' => 'Uturuki')))->setISOCode3('TUR'),
			StoreCountry::create('TM', tr_(array('en' => 'Turkmenistan', 'sw' => 'Turkmenistan')))->setISOCode3('TKM'),
			StoreCountry::create('TC', tr_(array('en' => 'Turks & Caicos Islands', 'sw' => 'Visiwa vya Turks & Caicos')))->setISOCode3('TCA'),
			StoreCountry::create('TV', tr_(array('en' => 'Tuvalu', 'sw' => 'Tuvalu')))->setISOCode3('TUV'),
			StoreCountry::create('UM', tr_(array('en' => 'U.S. Outlying Islands', 'sw' => 'Visiwa vya Amerika vilivyo mbali')))->setISOCode3('UMI'),
			StoreCountry::create('VI', tr_(array('en' => 'U.S. Virgin Islands', 'sw' => 'Visiwa vya Virgin vya Merika')))->setISOCode3('VIR'),
			StoreCountry::create('UG', tr_(array('en' => 'Uganda', 'sw' => 'Uganda')))->setISOCode3('UGA'),
			StoreCountry::create('UA', tr_(array('en' => 'Ukraine', 'sw' => 'Ukraine')))->setISOCode3('UKR'),
			StoreCountry::create('AE', tr_(array('en' => 'United Arab Emirates', 'sw' => 'Falme za Kiarabu')))->setISOCode3('ARE'),
			StoreCountry::create('GB', tr_(array('en' => 'United Kingdom', 'sw' => 'Uingereza')))->setISOCode3('GBR'),
			StoreCountry::create('US', tr_(array('en' => 'United States', 'sw' => 'Marekani')))->setISOCode3('USA')
				->addRegion(new StoreRegion('AL', tr_(array('en' => 'Alabama', 'sw' => 'Alabama'))))
				->addRegion(new StoreRegion('AK', tr_(array('en' => 'Alaska', 'sw' => 'Alaska'))))
				->addRegion(new StoreRegion('AZ', tr_(array('en' => 'Arizona', 'sw' => 'Arizona'))))
				->addRegion(new StoreRegion('AR', tr_(array('en' => 'Arkansas', 'sw' => 'Arkansas'))))
				->addRegion(new StoreRegion('CA', tr_(array('en' => 'California', 'sw' => 'California'))))
				->addRegion(new StoreRegion('CO', tr_(array('en' => 'Colorado', 'sw' => 'Colorado'))))
				->addRegion(new StoreRegion('CT', tr_(array('en' => 'Connecticut', 'sw' => 'Connecticut'))))
				->addRegion(new StoreRegion('DE', tr_(array('en' => 'Delaware', 'sw' => 'Delaware'))))
				->addRegion(new StoreRegion('FL', tr_(array('en' => 'Florida', 'sw' => 'Florida'))))
				->addRegion(new StoreRegion('GA', tr_(array('en' => 'Georgia', 'sw' => 'Georgia'))))
				->addRegion(new StoreRegion('HI', tr_(array('en' => 'Hawaii', 'sw' => 'Hawaii'))))
				->addRegion(new StoreRegion('ID', tr_(array('en' => 'Idaho', 'sw' => 'Idaho'))))
				->addRegion(new StoreRegion('IL', tr_(array('en' => 'Illinois', 'sw' => 'Illinois'))))
				->addRegion(new StoreRegion('IN', tr_(array('en' => 'Indiana', 'sw' => 'Indiana'))))
				->addRegion(new StoreRegion('IA', tr_(array('en' => 'Iowa', 'sw' => 'Iowa'))))
				->addRegion(new StoreRegion('KS', tr_(array('en' => 'Kansas', 'sw' => 'Kansas'))))
				->addRegion(new StoreRegion('KY', tr_(array('en' => 'Kentucky', 'sw' => 'Kentucky'))))
				->addRegion(new StoreRegion('LA', tr_(array('en' => 'Louisiana', 'sw' => 'Louisiana'))))
				->addRegion(new StoreRegion('ME', tr_(array('en' => 'Maine', 'sw' => 'Maine'))))
				->addRegion(new StoreRegion('MD', tr_(array('en' => 'Maryland', 'sw' => 'Maryland'))))
				->addRegion(new StoreRegion('MA', tr_(array('en' => 'Massachusetts', 'sw' => 'Massachusetts'))))
				->addRegion(new StoreRegion('MI', tr_(array('en' => 'Michigan', 'sw' => 'Michigan'))))
				->addRegion(new StoreRegion('MN', tr_(array('en' => 'Minnesota', 'sw' => 'Minnesota'))))
				->addRegion(new StoreRegion('MS', tr_(array('en' => 'Mississippi', 'sw' => 'Mississippi'))))
				->addRegion(new StoreRegion('MO', tr_(array('en' => 'Missouri', 'sw' => 'Missouri'))))
				->addRegion(new StoreRegion('MT', tr_(array('en' => 'Montana', 'sw' => 'Montana'))))
				->addRegion(new StoreRegion('NE', tr_(array('en' => 'Nebraska', 'sw' => 'Nebraska'))))
				->addRegion(new StoreRegion('NV', tr_(array('en' => 'Nevada', 'sw' => 'Nevada'))))
				->addRegion(new StoreRegion('NH', tr_(array('en' => 'New Hampshire', 'sw' => 'New Hampshire'))))
				->addRegion(new StoreRegion('NJ', tr_(array('en' => 'New Jersey', 'sw' => 'New Jersey'))))
				->addRegion(new StoreRegion('NM', tr_(array('en' => 'New Mexico', 'sw' => 'Mexico Mpya'))))
				->addRegion(new StoreRegion('NY', tr_(array('en' => 'New York', 'sw' => 'New York'))))
				->addRegion(new StoreRegion('NC', tr_(array('en' => 'North Carolina', 'sw' => 'Carolina Kaskazini'))))
				->addRegion(new StoreRegion('ND', tr_(array('en' => 'North Dakota', 'sw' => 'Dakota Kaskazini'))))
				->addRegion(new StoreRegion('OH', tr_(array('en' => 'Ohio', 'sw' => 'Ohio'))))
				->addRegion(new StoreRegion('OK', tr_(array('en' => 'Oklahoma', 'sw' => 'Oklahoma'))))
				->addRegion(new StoreRegion('OR', tr_(array('en' => 'Oregon', 'sw' => 'Oregon'))))
				->addRegion(new StoreRegion('PA', tr_(array('en' => 'Pennsylvania', 'sw' => 'Pennsylvania'))))
				->addRegion(new StoreRegion('RI', tr_(array('en' => 'Rhode Island', 'sw' => 'Kisiwa cha Rhode'))))
				->addRegion(new StoreRegion('SC', tr_(array('en' => 'South Carolina', 'sw' => 'Carolina Kusini'))))
				->addRegion(new StoreRegion('SD', tr_(array('en' => 'South Dakota', 'sw' => 'Dakota Kusini'))))
				->addRegion(new StoreRegion('TN', tr_(array('en' => 'Tennessee', 'sw' => 'Tennessee'))))
				->addRegion(new StoreRegion('TX', tr_(array('en' => 'Texas', 'sw' => 'Texas'))))
				->addRegion(new StoreRegion('UT', tr_(array('en' => 'Utah', 'sw' => 'Utah'))))
				->addRegion(new StoreRegion('VT', tr_(array('en' => 'Vermont', 'sw' => 'Vermont'))))
				->addRegion(new StoreRegion('VA', tr_(array('en' => 'Virginia', 'sw' => 'Virginia'))))
				->addRegion(new StoreRegion('WA', tr_(array('en' => 'Washington', 'sw' => 'Washington'))))
				->addRegion(new StoreRegion('DC', tr_(array('en' => 'Washington D.C. (District of Columbia)', 'sw' => 'Washington DC (Wilaya ya Columbia)'))))
				->addRegion(new StoreRegion('WV', tr_(array('en' => 'West Virginia', 'sw' => 'Virginia Magharibi'))))
				->addRegion(new StoreRegion('WI', tr_(array('en' => 'Wisconsin', 'sw' => 'Wisconsin'))))
				->addRegion(new StoreRegion('WY', tr_(array('en' => 'Wyoming', 'sw' => 'Wyoming')))),
			StoreCountry::create('UY', tr_(array('en' => 'Uruguay', 'sw' => 'Uruguay')))->setISOCode3('URY'),
			StoreCountry::create('UZ', tr_(array('en' => 'Uzbekistan', 'sw' => 'Uzbekistan')))->setISOCode3('UZB'),
			StoreCountry::create('VU', tr_(array('en' => 'Vanuatu', 'sw' => 'Vanuatu')))->setISOCode3('VUT'),
			StoreCountry::create('VA', tr_(array('en' => 'Vatican City', 'sw' => 'Jiji la Vatican')))->setISOCode3('VAT'),
			StoreCountry::create('VE', tr_(array('en' => 'Venezuela', 'sw' => 'Venezuela')))->setISOCode3('VEN'),
			StoreCountry::create('VN', tr_(array('en' => 'Vietnam', 'sw' => 'Vietnam')))->setISOCode3('VNM'),
			StoreCountry::create('WF', tr_(array('en' => 'Wallis & Futuna', 'sw' => 'Wallis & Futuna')))->setISOCode3('WLF'),
			StoreCountry::create('EH', tr_(array('en' => 'Western Sahara', 'sw' => 'Sahara Magharibi')))->setISOCode3('ESH'),
			StoreCountry::create('YE', tr_(array('en' => 'Yemen', 'sw' => 'Yemen')))->setISOCode3('YEM'),
			StoreCountry::create('ZM', tr_(array('en' => 'Zambia', 'sw' => 'Zambia')))->setISOCode3('ZMB'),
			StoreCountry::create('ZW', tr_(array('en' => 'Zimbabwe', 'sw' => 'Zimbabwe')))->setISOCode3('ZWE')
		);
		usort($list, function(StoreCountry $a, StoreCountry $b) {
			return ($a->name > $b->name) ? 1 : -1;
		});
		return $list;
	}
	
}
