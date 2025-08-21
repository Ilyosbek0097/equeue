<?php
namespace tests\unit\modules\equeue\models;

use app\modules\equeue\models\Service;
use Codeception\Test\Unit;

class ServiceTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // This function is executed before each test
    protected function _before()
    {
        // It's a good practice to clean up the table before each test
        // to ensure tests are isolated.
        Service::deleteAll();
    }

    // Test that a service with valid data can be saved
    public function testServiceCreationSuccess()
    {
        $service = new Service([
            'name' => 'Test Service',
            'prefix' => 'T',
        ]);

        $this->assertTrue($service->save(), 'Service should be saved with valid data.');

        // Verify the record in the database
        $this->tester->seeRecord(Service::class, [
            'name' => 'Test Service',
            'prefix' => 'T',
        ]);
    }

    // Test that a service with invalid data fails validation
    public function testServiceCreationFail()
    {
        // Missing name
        $service = new Service([
            'prefix' => 'F',
        ]);
        $this->assertFalse($service->validate(), 'Service should fail validation without a name.');
        $this->assertArrayHasKey('name', $service->getErrors(), 'Error message should exist for name.');

        // Missing prefix
        $service = new Service([
            'name' => 'Fail Service',
        ]);
        $this->assertFalse($service->validate(), 'Service should fail validation without a prefix.');
        $this->assertArrayHasKey('prefix', $service->getErrors(), 'Error message should exist for prefix.');
    }

    // Test that prefix must be unique
    public function testPrefixUniqueness()
    {
        // Create a service first
        $this->tester->haveRecord(Service::class, ['name' => 'Unique Service 1', 'prefix' => 'U']);

        // Try to create another with the same prefix
        $service2 = new Service([
            'name' => 'Unique Service 2',
            'prefix' => 'U',
        ]);
        $this->assertFalse($service2->save(), 'Service should not save with a duplicate prefix.');
        $this->assertArrayHasKey('prefix', $service2->getErrors(), 'Error message should exist for duplicate prefix.');
    }
}
