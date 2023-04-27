<?php

namespace Serato\SwsApp\Validation; 
use InvalidArgumentException;
use Rakit\Validation\Validator; 

/**
 * Class InputValidation
 * @package Serato\SwsApp\Validation
 */
trait InputValidation {
    protected $validator;

    public function __construct() {
        $this->validator = new Validator();
    }

    public function isValidUsername($username) {
        $validation = $this->validator->make(['username' => $username], [
            'username' => 'required|regex:/^[a-zA-Z0-9](?:[a-zA-Z0-9]|-(?=[a-zA-Z0-9]))*[a-zA-Z0-9]$/'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            throw new InvalidArgumentException("Invalid username: $username");
        }
        return true;
    }

    public function isValidEmail($email) {
        $validation = $this->validator->make(['email' => $email], [
            'email' => 'required|email'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            throw new InvalidArgumentException("Invalid email address: $email");
        }
        return true;
    }

    public function isValidPassword($password) {
        $validation = $this->validator->make(['password' => $password], [
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]*$/'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            throw new InvalidArgumentException("Invalid password: $password");
        }
        return true;
    }

    public function isValidUrl($url) {
        $validation = $this->validator->make(['url' => $url], [
            'url' => 'required|url'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            throw new InvalidArgumentException("Invalid URL: $url");
        }
        return true;
    }

    public function isValidPhoneNumber($phoneNumber) {
        $validation = $this->validator->make(['phone_number' => $phoneNumber], [
            'phone_number' => 'required|regex:/^\+?\d{1,4}[-.\s]?\(?\d{1,3}?\)?[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9}$/'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            throw new InvalidArgumentException("Invalid phone number: $phoneNumber");
        }
        return true;
    }

    public function isValidDate($date, $format = 'Y-m-d') {
        $validation = $this->validator->make(['date' => $date], [
            'date' => "required|date_format:$format"
        ]);

        $validation->validate();

        if ($validation->fails()) {
            throw new InvalidArgumentException("Invalid date: $date");
        }
        return true;
    }

    public function isValidIpAddress($ipAddress) {
        $validation = $this->validator->make(['ip_address' => $ipAddress], [
            'ip_address' => 'required|ip'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            throw new InvalidArgumentException("Invalid IP address: $ipAddress");
        }
        return true;
    }
}
