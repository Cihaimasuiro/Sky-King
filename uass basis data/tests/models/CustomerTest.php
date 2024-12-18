<?php
require_once __DIR__ . '/../../models/Customer.php';

class CustomerTest {
    private $customer;
    
    public function __construct() {
        $this->customer = new Customer();
    }
    
    public function testCreateCustomer() {
        $data = [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'address' => 'Test Address'
        ];
        
        $result = $this->customer->createCustomer($data);
        assert($result === true, "Failed to create customer");
    }
    
    public function testGetCustomer() {
        // Add test implementation
    }
    
    public function testUpdateCustomer() {
        // Add test implementation
    }
    
    public function testDeleteCustomer() {
        // Add test implementation
    }
    
    public function runAllTests() {
        $this->testCreateCustomer();
        $this->testGetCustomer();
        $this->testUpdateCustomer();
        $this->testDeleteCustomer();
        echo "All tests completed successfully!\n";
    }
}

// Run tests if file is executed directly
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    $test = new CustomerTest();
    $test->runAllTests();
}
