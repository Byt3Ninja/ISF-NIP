<?php

namespace Tests\Support;

use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\DNSGetRecordWrapper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as FrameworkValidator;
use Tests\Support\Fakes\FakeDnsRecords;

/**
 * The `email:dns` rule asks a DNS server whether the address's domain can receive mail, so a
 * test that submits such a form fails wherever the suite runs without a resolver. After
 * fakeEmailDns() the same check reads a fixed list of domains and nothing leaves the machine.
 * Only the lookup is replaced: the check itself, and every other email rule, stay the real ones.
 */
trait FakesEmailDns
{
    /** @param string[] $mailDomains the only domains that resolve */
    protected function fakeEmailDns(array $mailDomains = ['gmail.com']): void
    {
        $this->app->instance(DNSGetRecordWrapper::class, new FakeDnsRecords($mailDomains));

        // The framework builds `dns` with `new`, out of the container's reach. Asked for by
        // class name instead, the same check is built by the container, which hands it the
        // lookup bound above.
        Validator::resolver(fn (...$arguments) => new class(...$arguments) extends FrameworkValidator {
            public function validateEmail($attribute, $value, $parameters)
            {
                return parent::validateEmail($attribute, $value, array_map(
                    fn ($check) => $check === 'dns' ? DNSCheckValidation::class : $check,
                    $parameters
                ));
            }
        });
    }
}
