<?php

namespace Tests\Support\Fakes;

use Egulias\EmailValidator\Validation\DNSGetRecordWrapper;
use Egulias\EmailValidator\Validation\DNSRecords;

/** Answers DNS lookups from a fixed list of mail domains instead of asking the network. */
class FakeDnsRecords extends DNSGetRecordWrapper
{
    /** @param string[] $mailDomains domains that have a mail server; every other host has no records */
    public function __construct(private array $mailDomains)
    {
    }

    public function getRecords(string $host, int $type): DNSRecords
    {
        if (! ($type & DNS_MX) || ! in_array(strtolower($host), $this->mailDomains, true)) {
            return new DNSRecords([]);
        }

        // The shape dns_get_record() gives an MX answer.
        return new DNSRecords([
            ['host' => $host, 'class' => 'IN', 'ttl' => 300, 'type' => 'MX', 'pri' => 10, 'target' => 'mx.' . $host],
        ]);
    }
}
