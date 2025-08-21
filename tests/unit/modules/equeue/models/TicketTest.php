<?php
namespace tests\unit\modules\equeue\models;

use app\modules\equeue\models\Service;
use app\modules\equeue\models\Ticket;
use Codeception\Test\Unit;

class TicketTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // Clean up tables to ensure test isolation
        Ticket::deleteAll();
        Service::deleteAll();
    }

    public function testTicketCreation()
    {
        $service = $this->tester->haveRecord(Service::class, ['name' => 'Kassa', 'prefix' => 'K']);

        $ticket = new Ticket([
            'service_id' => $service->id,
            'ticket_number' => 'K1',
            'status' => Ticket::STATUS_NEW,
        ]);

        $this->assertTrue($ticket->save(), 'Ticket should be saved with valid data.');
        $this->tester->seeRecord(Ticket::class, ['ticket_number' => 'K1']);
    }

    public function testTicketNumberGeneration()
    {
        // Create two services
        $serviceA = $this->tester->haveRecord(Service::class, ['name' => 'Service A', 'prefix' => 'A']);
        $serviceB = $this->tester->haveRecord(Service::class, ['name' => 'Service B', 'prefix' => 'B']);

        // Generate first ticket for Service A
        $number1_A = Ticket::generateNextTicketNumber($serviceA->id);
        $this->assertEquals('A1', $number1_A, 'First ticket for service A should be A1.');
        $this->tester->haveRecord(Ticket::class, [
            'service_id' => $serviceA->id,
            'ticket_number' => $number1_A,
            'created_at' => time()
        ]);

        // Generate second ticket for Service A
        $number2_A = Ticket::generateNextTicketNumber($serviceA->id);
        $this->assertEquals('A2', $number2_A, 'Second ticket for service A should be A2.');
        $this->tester->haveRecord(Ticket::class, [
            'service_id' => $serviceA->id,
            'ticket_number' => $number2_A,
            'created_at' => time()
        ]);

        // Generate first ticket for Service B
        $number1_B = Ticket::generateNextTicketNumber($serviceB->id);
        $this->assertEquals('B1', $number1_B, 'First ticket for service B should be B1.');
    }

    public function testTicketServiceRelation()
    {
        $service = $this->tester->haveRecord(Service::class, ['name' => 'Relations Test', 'prefix' => 'R']);
        // The haveRecord method returns the model instance itself
        $ticketModel = $this->tester->grabRecord(Ticket::class, [
            'id' => $this->tester->haveRecord(Ticket::class, ['service_id' => $service->id, 'ticket_number' => 'R1'])
        ]);

        $this->assertNotNull($ticketModel->service, 'Ticket should have a related service.');
        $this->assertEquals('Relations Test', $ticketModel->service->name, 'Related service name should match.');
    }
}
