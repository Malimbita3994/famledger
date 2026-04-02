<?php

namespace App\Services;

use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Contract\Firestore;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Contract\Storage;
use Kreait\Firebase\Factory;

class FirebaseClient
{
    public function __construct(private readonly Factory $factory)
    {
    }

    public function auth(): Auth
    {
        return $this->factory->createAuth();
    }

    public function messaging(): Messaging
    {
        return $this->factory->createMessaging();
    }

    public function database(): Database
    {
        return $this->factory->createDatabase();
    }

    public function firestore(): Firestore
    {
        return $this->factory->createFirestore();
    }

    public function storage(): Storage
    {
        return $this->factory->createStorage();
    }
}

