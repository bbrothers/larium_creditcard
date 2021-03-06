<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/*
 * This file is part of the Larium CreditCard package.
 *
 * (c) Andreas Kollaros <andreas@larium.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Larium\CreditCard;

/**
 * CreditCard class acts as value object.
 *
 * @author  Andreas Kollaros <andreas@larium.net>
 */
final class CreditCard
{
    const VISA               = 'visa';
    const MASTER             = 'master';
    const DISCOVER           = 'discover';
    const AMEX               = 'american_express';
    const DINERS_CLUB        = 'diners_club';
    const JCB                = 'jcb';
    const SWITCH_BRAND       = 'switch';
    const SOLO               = 'solo';
    const DANKORT            = 'dankort';
    const MAESTRO            = 'maestro';
    const FORBRUGSFORENINGEN = 'forbrugsforeningen';
    const LASER              = 'laser';
    const UNIONPAY           = 'unionpay';

    /**
     * Card holder name.
     * Should be in upper case.
     *
     * @var string
     */
    private $holderName;

    /**
     * Expire date of card as value object
     *
     * @var ExpiryDate
     */
    private $expiryDate;

    /**
     * The brand of card.
     *
     * @var mixed|false
     */
    private $brand;

    /**
     * The number of card.
     *
     * @var string
     */
    private $number;

    /**
     * The verification value of card (cvv).
     * 3 or 4 digits.
     *
     * @var integer
     */
    private $cvv;

    /**
     * Whether card is require verification value to be present.
     *
     * @var bool
     */
    private $requireCvv = true;

    /**
     * Token stored from a real credit card and can be used for purchases.
     *
     * @var Token
     */
    private $token;

    /**
     * @var string
     */
    private $bin;

    /**
     * @var string
     */
    private $issuingBank;

    /**
     * The iso alpha 3 country code.
     * @var string
     */
    private $country;

    public function __construct(array $options = array())
    {
        $default = array(
            'holderName' => null,
            'month'      => 1,
            'year'       => 1970,
            'brand'      => '',
            'number'     => null,
            'cvv'        => null,
            'requireCvv' => true,
            'token'      => null
        );

        $options = array_intersect_key($options, $default);
        $options = array_replace($default, $options);

        $month = $options['month'];
        $year  = $options['year'];
        $brand = $options['brand'];
        $token = $options['token'];

        unset($options['month'], $options['year'], $options['brand'], $options['token']);

        $this->setProperties($month, $year, $brand, $token, $options);
    }

    private function setProperties($month, $year, $brand, $token, $options)
    {
        foreach ($options as $prop => $value) {
            $this->$prop = $value;
        }

        $this->holderName = strtoupper($this->holderName);

        $this->expiryDate = new ExpiryDate($month, $year);

        $this->detectBrand($brand);

        $this->token($token);

        $this->bin = substr($this->number, 0, 6);
    }

    private function token($token)
    {
        if (null === $token) {
            return;
        }

        if ($token instanceof Token) {
            $this->token = $token;

            return;
        }

        $this->token = new Token($token);
    }

    /**
     * @param string $brand
     * @return void
     */
    private function detectBrand($brand = '')
    {
        $detector = new CreditCardDetector();

        if (false === $this->brand = $detector->detect($this->number)) {
            $this->brand = $brand;
        };
    }

    /**
     * @param string $prop
     * @param mixed $value
     * @return CreditCard
     */
    private function with($prop, $value)
    {
        $card = clone $this;

        $card->$prop = $value;

        return $card;
    }

    /**
     * Gets the number of card.
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Sets card number.
     *
     * @param  string $number
     * @return CreditCard
     */
    public function withNumber($number)
    {
        $card = $this->with('number', $number);
        $card->detectBrand();
        $card->token = null;
        $card->bin = substr($number, 0, 6);

        return $card;
    }

    /**
     * Gets card holder name.
     *
     * @return string
     */
    public function getHolderName()
    {
        return $this->holderName;
    }

    /**
     * Sets card holder name.
     *
     * @param  string $holderName
     * @return CreditCard
     */
    public function withHolderName($holderName)
    {
        $holderName = strtoupper($holderName);

        return $this->with('holderName', $holderName);
    }

    /**
     * Gets expiry date card.
     *
     * @return ExpiryDate
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Sets expiry month of card.
     *
     * @param  ExpiryDate $expiryDate
     * @return CreditCard
     */
    public function withExpiryDate(ExpiryDate $expiryDate)
    {
        return $this->with('expiryDate', $expiryDate);
    }

    /**
     * Gets the brand of card.
     *
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Sets the brand of card.
     *
     * @param  string $brand
     * @return CreditCard
     */
    public function withBrand($brand)
    {
        return $this->with('brand', $brand);
    }

    /**
     * Gets card verification value (cvv).
     *
     * @return integer
     */
    public function getCvv()
    {
        return $this->cvv;
    }

    /**
     * Sets card verification value.
     *
     * @param  integer $cvv
     * @return CreditCard
     */
    public function withCvv($cvv)
    {
        return $this->with('cvv', $cvv);
    }

    /**
     * Check if cvv is required for credit card validation.
     *
     * @return boolean
     */
    public function isRequireCvv()
    {
        return $this->requireCvv;
    }

    /**
     * Gets referenece token of a credit card.
     *
     * @return Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Sets token value.
     *
     * @param  Token $token
     * @return CreditCard
     */
    public function withToken(Token $token)
    {
        $card = $this->with('token', $token);

        if (null !== $card->number) {
            $lastDigits = strlen($card->number) <= 4
                ? $card->number :
                substr($card->number, -4);
            $card->number = "XXXX-XXXX-XXXX-" . $lastDigits;
        }

        $card->cvv = null;

        return $card;
    }

    /**
     * Checks whether credit card has stored a Token reference or not.
     *
     * @return boolean
     */
    public function hasToken()
    {
        return null !== $this->token;
    }

    public function __clone()
    {
        if ($this->expiryDate) {
            $this->expiryDate = clone $this->expiryDate;
        }
    }

    public function getBin()
    {
        return $this->bin;
    }

    public function withIssuingBank($issuingBank)
    {
        return $this->with('issuingBank', $issuingBank);
    }

    public function getIssuingBank()
    {
        return $this->issuingBank;
    }

    public function withCountry($country)
    {
        return $this->with('country', $country);
    }

    public function getCountry()
    {
        return $this->country;
    }
}
