<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Adapters;

use InvalidArgumentException;

final class AdapterFactory
{
    public function make(string $adapter): SourceAdapterInterface
    {
        return match (strtolower(trim($adapter))) {
            'rest', 'rest_api', 'api' => new RestApiAdapter(),
            'json' => new JsonAdapter(),
            'csv' => new CsvAdapter(),
            'xml' => new XmlAdapter(),
            'soap' => new SoapAdapter(),
            'sql', 'mysql', 'mariadb', 'postgres', 'pgsql', 'oracle', 'sqlite', 'sqlserver', 'mssql' => new SqlAdapter(),
            'ldap', 'active_directory', 'ad' => new LdapAdapter(),
            'ftp' => new FtpAdapter(),
            'sftp' => new SftpAdapter(),
            'email', 'imap' => new EmailAdapter(),
            'webhook' => new JsonAdapter(),
            default => throw new InvalidArgumentException('Unsupported adapter: ' . $adapter),
        };
    }

    /**
     * @return array<int,string>
     */
    public function supported(): array
    {
        return [
            'rest',
            'soap',
            'csv',
            'json',
            'xml',
            'sql',
            'ldap',
            'active_directory',
            'ftp',
            'sftp',
            'email',
            'webhook',
        ];
    }
}
