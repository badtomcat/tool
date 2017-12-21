<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/21
 * Time: 14:33
 */

namespace Badtomcat\Tool\Db;

use Badtomcat\Db\Connection\MysqlPdoConn;
use Badtomcat\Db\MySqlDbReflection;
use Badtomcat\Db\MysqlTableReflection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MySqlTable extends Command
{
    /**
     * @var MysqlPdoConn $connection
     */
    protected $connection;
    protected $database = '';
//    public function __construct($database='', $host = '127.0.0.1', $user = 'root', $pass = 'root', $charset = 'utf8', $port = 3306)
//    {
//        parent::__construct();
//
//    }

    public function configure()
    {
        $this->addOption('db','d',InputOption::VALUE_OPTIONAL,"Db name",'garri');
        $this->addOption('host','host',InputOption::VALUE_OPTIONAL,"Host name",'127.0.0.1');
        $this->addOption('user','u',InputOption::VALUE_OPTIONAL,"user",'root');
        $this->addOption('pass','p',InputOption::VALUE_OPTIONAL,"password",'root');
        $this->addOption('charset','c',InputOption::VALUE_OPTIONAL,"charset",'utf8');
        $this->addOption('port','port',InputOption::VALUE_OPTIONAL,"port",3306);
//
//

        $this->addArgument('export',InputOption::VALUE_REQUIRED,'Export name/pk');
        $this->addArgument('tb',InputOption::VALUE_OPTIONAL,"table name");

        $this->setName("table");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $database = $input->getOption('db');
        $host = $input->getOption('host');
        $user = $input->getOption('user');
        $password = $input->getOption('pass');
        $charset = $input->getOption('charset');
        $port = $input->getOption('port');

        $this->connect(compact('database', 'host', 'user', 'password', 'charset', 'port'));

        $e = $input->getArgument('export');
        switch ($e)
        {
            case 'name':
                $this->exportTables($output);
                break;
            case 'pk':
                $this->exportPk($input,$output);
                break;
            default:
                $output->writeln('invalid option export, name/pk is valid.');
                break;
        }
    }

    protected function connect(array $conn)
    {
        $this->connection = new MysqlPdoConn($conn);
    }

    protected function exportTables(OutputInterface $output)
    {
        $info = new MySqlDbReflection($this->connection);
        foreach ($info->getTableNames() as $tableName)
        {
            $output->writeln($tableName);
        }
    }

    protected function exportPk(InputInterface $input,OutputInterface $output)
    {
        $tb = $input->getArgument("tb");

        if (empty($tb))
        {
            $info = new MySqlDbReflection($this->connection);
            $tb = $info->getTableNames();
        }

        $info = new MysqlTableReflection("",$this->connection);
        foreach ($tb as $item)
        {
            $info->setTableName($item);
            $output->writeln("$item\t".join(",",$info->getPk()));
        }
    }
}