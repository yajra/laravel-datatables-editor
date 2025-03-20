<?php

namespace Yajra\DataTables\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Yajra\DataTables\Tests\TestCase;

class DataTablesEditorActionTest extends TestCase
{
    #[Test]
    public function it_can_process_custom_action_request()
    {
        $response = $this->postJson('users', [
            'action' => 'remove2fa',
            'data' => [],
        ]);

        $data = $response->json();
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('2FA has been removed successfully.', $data['message']);
    }
}
