<?php

namespace App\Service\Database;

/**
 * Holds the rules that describe how personal / sensitive data has to be
 * replaced while dumping the database. The rules are keyed by table name and
 * column name so the {@see AnonymizedDatabaseDumper} can look them up quickly
 * while streaming the rows.
 */
class DatabaseAnonymizer
{
    // Available anonymisation strategies.
    public const NULLIFY = 'nullify';
    public const BLANK = 'blank';
    public const REDACTED = 'redacted';
    public const EMAIL = 'email';
    public const FIRSTNAME = 'firstname';
    public const LASTNAME = 'lastname';
    public const FULLNAME = 'fullname';
    public const STREET = 'street';
    public const PLZ = 'plz';
    public const CITY = 'city';
    public const PHONE = 'phone';
    public const IBAN = 'iban';
    public const BIC = 'bic';
    public const BANK = 'bank';
    public const ACCOUNT_HOLDER = 'account_holder';
    public const DATE_FIXED = 'date_fixed';

    /**
     * Applies a single anonymisation strategy to a value. The whole row is
     * passed as well so strategies can build unique but reproducible values
     * (e.g. based on the primary key).
     *
     * @param array<string, mixed> $row
     */
    public function anonymize(string $table, string $column, mixed $value, array $row): mixed
    {
        // Never touch values that are NULL to begin with – keep the dump close
        // to the original structure.
        if ($value === null) {
            return null;
        }

        $tableStrategy = $this->getRulesForTable($table);
        if ($tableStrategy === null) {
            return $value;
        }

        $strategy = $tableStrategy[$column] ?? null;
        if ($strategy === null) {
            return $value;
        }

        $id = isset($row['id']) ? (string)$row['id'] : substr(md5(serialize($row)), 0, 8);

        return match ($strategy) {
            self::NULLIFY => null,
            self::BLANK => '',
            self::REDACTED => 'anonymisiert',
            self::EMAIL => 'user' . $id . '@example.invalid',
            self::FIRSTNAME => 'Vorname' . $id,
            self::LASTNAME => 'Nachname' . $id,
            self::FULLNAME => 'Person ' . $id,
            self::STREET => 'Musterstraße ' . $id,
            self::PLZ => '00000',
            self::CITY => 'Musterstadt',
            self::PHONE => '+49 000 0000000',
            self::IBAN => 'DE00000000000000000000',
            self::BIC => 'TESTDEFFXXX',
            self::BANK => 'Musterbank',
            self::ACCOUNT_HOLDER => 'Kontoinhaber ' . $id,
            self::DATE_FIXED => '2000-01-01',
            default => (string)$value,
        };
    }

    /**
     * @return array<string, array<string, string>> table => (column => strategy)
     */
    private function getRules(): array
    {
        return [
            'fos_user' => [
                'vorname' => self::FIRSTNAME,
                'nachname' => self::LASTNAME,
                'birthday' => self::DATE_FIXED,
                'app_token' => self::NULLIFY,
                'confirmation_token_app' => self::NULLIFY,
                'app_communication_token' => self::NULLIFY,
                'app_detection_token' => self::NULLIFY,
                'app_imei' => self::NULLIFY,
                'app_os' => self::NULLIFY,
                'app_device' => self::NULLIFY,
                'auth0id' => self::NULLIFY,
                'email' => self::EMAIL,
                'keycloak_id' => self::NULLIFY,
                'invitation_token' => self::NULLIFY,
            ],
            'kind' => [
                'vorname' => self::FIRSTNAME,
                'nachname' => self::LASTNAME,
                'geburtstag' => self::DATE_FIXED,
                'bemerkung' => self::REDACTED,
                'internal_notice' => self::REDACTED,
                'chronical_deseas' => self::REDACTED,
            ],
            'personenberechtigter' => [
                'vorname' => self::FIRSTNAME,
                'nachname' => self::LASTNAME,
                'strasse' => self::STREET,
                'adresszusatz' => self::BLANK,
                'plz' => self::PLZ,
                'stadt' => self::CITY,
                'phone' => self::PHONE,
                'email' => self::EMAIL,
                'notfallkontakt' => self::REDACTED,
            ],
            'stammdaten' => [
                'name' => self::LASTNAME,
                'vorname' => self::FIRSTNAME,
                'strasse' => self::STREET,
                'adresszusatz' => self::BLANK,
                'notfallkontakt' => self::REDACTED,
                'iban' => self::IBAN,
                'bic' => self::BIC,
                'kontoinhaber' => self::ACCOUNT_HOLDER,
                'plz' => self::PLZ,
                'stadt' => self::CITY,
                'sec_code' => self::NULLIFY,
                'email' => self::EMAIL,
                'abholberechtigter' => self::REDACTED,
                'notfall_name' => self::FULLNAME,
                'confirmation_code' => self::NULLIFY,
                'ip_adresse' => self::NULLIFY,
                'phone_number' => self::PHONE,
                'kiga_of_kids' => self::BLANK,
                'email_double_input' => self::EMAIL,
            ],
            'geschwister' => [
                'vorname' => self::FIRSTNAME,
                'nachname' => self::LASTNAME,
                'geburtsdatum' => self::DATE_FIXED,
            ],
            'payment_sepa' => [
                'iban' => self::IBAN,
                'bic' => self::BIC,
                'bank_name' => self::BANK,
                'kontoinhaber' => self::ACCOUNT_HOLDER,
            ],
            'sepa' => [
                'sepa_xml' => self::BLANK,
                'pdf' => self::BLANK,
            ],
            'rechnung' => [
                'pdf' => self::BLANK,
            ],
            'parent_sick_portal_access' => [
                'email' => self::EMAIL,
                'uri' => self::BLANK,
            ],
            'email_response' => [
                'reciever' => self::EMAIL,
                'payload' => self::BLANK,
                'message' => self::BLANK,
                'description' => self::BLANK,
            ],
            'log' => [
                'user' => self::REDACTED,
            ],
        ];
    }

    /**
     * @return array<string, string>|null the (column => strategy) map for a table or null when nothing has to be anonymised.
     */
    private function getRulesForTable(string $table): ?array
    {
        return $this->getRules()[$table] ?? null;
    }

}
