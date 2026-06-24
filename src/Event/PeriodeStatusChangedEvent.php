<?php

namespace App\Event;

/**
 * Event class untuk perubahan status periode
 * Dapat digunakan untuk trigger queue job kalkulasi
 */
class PeriodeStatusChangedEvent
{
    private int $periodeId;
    private string $oldStatus;
    private string $newStatus;
    private \DateTimeInterface $timestamp;

    public function __construct(int $periodeId, string $oldStatus, string $newStatus)
    {
        $this->periodeId = $periodeId;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->timestamp = new \DateTime();
    }

    public function getPeriodeId(): int
    {
        return $this->periodeId;
    }

    public function getOldStatus(): string
    {
        return $this->oldStatus;
    }

    public function getNewStatus(): string
    {
        return $this->newStatus;
    }

    public function getTimestamp(): \DateTimeInterface
    {
        return $this->timestamp;
    }

    /**
     * Cek apakah periode baru saja menjadi aktif
     */
    public function isActivated(): bool
    {
        return $this->newStatus === 'aktif' && $this->oldStatus !== 'aktif';
    }

    /**
     * Cek apakah periode baru saja selesai
     */
    public function isCompleted(): bool
    {
        return $this->newStatus === 'selesai' && $this->oldStatus !== 'selesai';
    }
}
