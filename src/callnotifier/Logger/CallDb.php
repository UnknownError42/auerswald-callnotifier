<?php
namespace callnotifier;

/**
 * Logs finished calls into a SQL database.
 *
 * To use this, setup the database table using the script
 * in docs/create-call-log.sql
 */
class Logger_CallDb extends Logger_CallBase
{
    /**
     * Create new detailler object
     *
     * @param string $dsn      PDO connection string, for example
     *                         'mysql:host=dojo;dbname=opengeodb'
     * @param string $username Database username
     * @param string $password Database password
     */
    public function __construct($dsn, $username, $password)
    {
        $this->db = new \PDO(
            $dsn, $username, $password,
            array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            )
        );
        $this->stmt = $this->db->prepare(
            'INSERT INTO finished ('
            . '  call_start'
            . ', call_end'
            . ', call_type'
            . ', call_from'
            . ', call_from_name'
            . ', call_from_location'
            . ', call_to'
            . ', call_to_name'
            . ', call_to_location'
            . ', call_length'
            . ') VALUES ('
            . '  :call_start'
            . ', :call_end'
            . ', :call_type'
            . ', :call_from'
            . ', :call_from_name'
            . ', :call_from_location'
            . ', :call_to'
            . ', :call_to_name'
            . ', :call_to_location'
            . ', :call_length'
            . ')'
        );
    }

    public function log($type, $arData)
    {
        if ($type != 'finishedCall') {
            return;
        }

        $call = $arData['call'];
        $this->addUnsetVars($call);

        $ret = $this->stmt->execute(
            array(
                'call_start'         => date('Y-m-d H:i:s', $call->start),
                'call_end'           => date('Y-m-d H:i:s', $call->end),
                'call_type'          => $call->type,
                'call_from'          => $call->from,
                'call_from_name'     => $call->fromName,
                'call_from_location' => $call->fromLocation,
                'call_to'            => $call->to,
                'call_to_name'       => $call->toName,
                'call_to_location'   => $call->toLocation,
                'call_length'        => $call->end - $call->start
            )
        );
        if ($ret === false) {
            throw new \Exception(
                'Error logging call to database: '
                . implode(' / ', $this->stmt->errorInfo())
            );
        }
    }

}

?>