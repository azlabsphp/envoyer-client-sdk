<?php

namespace Drewlabs\Envoyer\Drivers\Envoyer;

use Drewlabs\Envoyer\Contracts\NotificationResult;

class Result implements NotificationResult
{

    /** @var string|null */
    private $id;

    /** @var string|null */
    private $date;

    /** @var bool */
    private $ok;

    /**
     * Creates new result instance
     * 
     * @param string|null $id 
     * @param string|null $date 
     * @param bool $ok 
     */
    public function __construct(string $id = null, string $date = null, bool $ok = true)
    {
        $this->id = $id;
        $this->date = $date;
        $this->ok = $ok;
    }

    /**
     * Creates new class instance from provided dictionnary
     * 
     * @param array $attributes 
     * @return static 
     */
    public static function fromJson(array $attributes = [])
    {
        return new static(
            $attributes['id'] ?? null,
            date('Y-m-d H:i:s', isset($attributes['sent_at']) ? strtotime($attributes['sent_at']) : time()),
            boolval($attributes['sent'])
        );
    }

    public function date()
    {
        return $this->date;
    }

    public function id()
    {
        return $this->id;
    }

    public function isOk()
    {
        return $this->ok;
    }
}
