<?php

declare(strict_types=1);

namespace SOLPI\Companies\Entities;

use SOLPI\Core\BaseEntity;

final class Company extends BaseEntity
{
    private string $name;

    private ?string $tradeName = null;

    private ?string $document = null;

    private ?string $email = null;

    private ?string $phone = null;

    private ?string $website = null;

    private ?string $address = null;

    private ?string $city = null;

    private ?string $state = null;

    private ?string $zipCode = null;

    private array $settings = [];

    public function __construct(
        string $uuid,
        string $name
    ){

        parent::__construct($uuid);

        $this->name=$name;

    }

    public function name():string
    {
        return $this->name;
    }

    public function rename(string $name):static
    {
        $this->name=trim($name);

        return $this;
    }

    public function tradeName():?string
    {
        return $this->tradeName;
    }

    public function setTradeName(?string $tradeName):static
    {
        $this->tradeName=$tradeName;

        return $this;
    }

    public function document():?string
    {
        return $this->document;
    }

    public function setDocument(?string $document):static
    {
        $this->document=$document;

        return $this;
    }

    public function email():?string
    {
        return $this->email;
    }

    public function setEmail(?string $email):static
    {
        $this->email=$email;

        return $this;
    }

    public function phone():?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone):static
    {
        $this->phone=$phone;

        return $this;
    }

    public function website():?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website):static
    {
        $this->website=$website;

        return $this;
    }

    public function address():?string
    {
        return $this->address;
    }

    public function setAddress(?string $address):static
    {
        $this->address=$address;

        return $this;
    }

    public function city():?string
    {
        return $this->city;
    }

    public function setCity(?string $city):static
    {
        $this->city=$city;

        return $this;
    }

    public function state():?string
    {
        return $this->state;
    }

    public function setState(?string $state):static
    {
        $this->state=$state;

        return $this;
    }

    public function zipCode():?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zip):static
    {
        $this->zipCode=$zip;

        return $this;
    }

    public function settings():array
    {
        return $this->settings;
    }

    public function setSetting(string $key,mixed $value):static
    {
        $this->settings[$key]=$value;

        return $this;
    }

    public function toArray():array
    {
        return [

            'id'=>$this->id,

            'uuid'=>$this->uuid,

            'name'=>$this->name,

            'trade_name'=>$this->tradeName,

            'document'=>$this->document,

            'email'=>$this->email,

            'phone'=>$this->phone,

            'website'=>$this->website,

            'address'=>$this->address,

            'city'=>$this->city,

            'state'=>$this->state,

            'zip_code'=>$this->zipCode,

            'active'=>$this->active,

            'settings'=>$this->settings,

            'metadata'=>$this->metadata,

            'created_at'=>$this->createdAt->format('Y-m-d H:i:s'),

            'updated_at'=>$this->updatedAt->format('Y-m-d H:i:s')

        ];
    }
}
