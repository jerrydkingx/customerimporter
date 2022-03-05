<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Sabbir\Customerimporter\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Customer\Model\CustomerFactory;

class Import extends Command
{ 
    const NAME_ARGUMENT = "name";
    
        /**
     * @var DirectoryList
     */
    private $directoryList;
    private $state;
        public function __construct(
        DirectoryList $directoryList,        
        State $state,
        CustomerFactory $customerFactory,     
        string $name = null
    ) {
        parent::__construct($name);
        $this->directoryList = $directoryList;
        $this->state = $state;
        $this->customerFactory  = $customerFactory;
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $filename = $input->getArgument(self::NAME_ARGUMENT);       
        try {

            if ($input->getOption('sample-csv')) {
                    $file = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR . $filename ;
                    $rows = array_map('str_getcsv', file($file));
                    $header = array_shift($rows);
                    $csv = array();
                        foreach ($rows as $row) {
                            $csv[] = array_combine($header, $row);
                            $customer   = $this->customerFactory->create();
                            $customer->setEmail($row[2]); 
                            $customer->setFirstname($row[1]);
                            $customer->setLastname($row[0]);
                            $customer->save();
                        }
            }
            
            if ($input->getOption('sample-json')) {
                $file = file_get_contents($this->directoryList->getPath('var') . DIRECTORY_SEPARATOR . $filename);
                $decoded_json = json_decode($file);
                foreach ($decoded_json as $value) {
                            $customer   = $this->customerFactory->create();
                            $customer->setEmail($value->emailaddress); 
                            $customer->setFirstname($value->fname);
                            $customer->setLastname($value->lname);
                            $customer->save();
                }

            }
            // Similarly we can add more file type such as xml,sql etc
                        
        } catch (\Exception $e) {
            $output->writeln('<comment>An exception was thrown during area code setting:</comment>');
            $output->writeln($e->getMessage());
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("customer:import");
        $this->setDescription("Import customer");
        $this->setDefinition([
        new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
        new InputOption('sample-csv',null, InputOption::VALUE_OPTIONAL, 'import the csv file'),
        new InputOption('sample-json',null, InputOption::VALUE_OPTIONAL, 'import json file')
        ]);

        parent::configure();
    }
    
}

