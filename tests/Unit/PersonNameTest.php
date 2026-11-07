<?php

namespace Tests\Unit;

use InternetGuru\LaravelUser\Rules\PersonName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PersonNameTest extends TestCase
{
    public static function validNames(): array
    {
        return array_map(fn ($name) => [$name], [
            'Jan Novák', 'Li', "O'Brien", 'Jean-Luc Picard', 'J. R. R. Tolkien', 'X123', '123', '1Jan', '3M', 'Jan..',
        ]);
    }

    public static function invalidNames(): array
    {
        return array_map(fn ($name) => [$name], [
            '', '...', '  ', 'J', '7', 'Jan!', '- Jan', '.Jan', 'Jan <b>', null,
        ]);
    }

    public static function emailNames(): array
    {
        return [
            ['jan.novak@seznam.cz', 'Jan Novak'],
            ['jiri_pavelka@gmail.com', 'Jiri Pavelka'],
            ['ondřej.černý@seznam.cz', 'Ondřej Černý'],
            ['JOHN.DOE@x.com', 'John Doe'],
            ['j.k.rowling@x.com', 'J. K. Rowling'],
            ['st.jan@x.cz', 'St. Jan'],
            ['abc.d@x.cz', 'Abc D'],
            ['jan.k.@x.cz', 'Jan K.'],
            ['j_k.rowling@x.com', 'J K. Rowling'],
            ['j..k@x.cz', 'J. K'],
            ['x1.novak@x.cz', 'X1 Novak'],
            ['j.k.2@x.cz', 'J. K.'],
            ['q.1@x.cz', 'Q. 1'],
            ['j.@seznam.cz', 'J. Seznam'],
            ['petr-svoboda85@email.cz', 'Petr Svoboda'],
            ['jan.novak.2@x.cz', 'Jan Novak'],
            ['ab1@x.cz', 'Ab'],
            ['jan2novak@x.cz', 'Jan2Novak'],
            ['x123@seznam.cz', 'X123'],
            ['x__123@x.cz', 'X 123'],
            ['a1@x.cz', 'A1'],
            ['12345@x.cz', '12345'],
            ['007.bond@x.cz', '007 Bond'],
            ['jan+shop@gmail.com', 'Jan'],
            ['info@firma.cz', 'Info'],
            ['q@seznam.cz', 'Qseznam'],
            ['1@1.cz', '11'],
            ['ž@š.cz', 'Žš'],
            ['q@mail.firma.co.uk', 'Qmail Firma Co'],
            ['_@seznam.cz', 'Seznam'],
            ['._.@a.cz', 'A'],
        ];
    }

    #[DataProvider('validNames')]
    public function test_accepts_valid_name(string $name)
    {
        $this->assertTrue(PersonName::isValid($name));
    }

    #[DataProvider('invalidNames')]
    public function test_rejects_invalid_name(?string $name)
    {
        $this->assertFalse(PersonName::isValid($name));
    }

    #[DataProvider('emailNames')]
    public function test_derives_name_from_email(string $email, string $name)
    {
        $this->assertSame($name, PersonName::fromEmail($email));
    }
}
