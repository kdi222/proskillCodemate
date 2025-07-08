<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace enrol_programs\external;

/**
 * External API for get program list
 *
 * @group      openlms
 * @package    enrol_programs
 * @copyright  2023 Open LMS (https://www.openlms.net/)
 * @author     Farhan Karmali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @runTestsInSeparateProcesses
 * @covers \enrol_programs\external\get_programs
 */
final class get_programs_test extends \advanced_testcase {
    public function setUp(): void {
        global $CFG;
        require_once("$CFG->dirroot/lib/externallib.php");
        $this->resetAfterTest();
    }

    public function test_execute() {
        /** @var \enrol_programs_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('enrol_programs');

        $syscontext = \context_system::instance();
        $category1 = $this->getDataGenerator()->create_category([]);
        $catcontext1 = \context_coursecat::instance($category1->id);

        $cohort1 = $this->getDataGenerator()->create_cohort();
        $cohort2 = $this->getDataGenerator()->create_cohort();

        $admin = get_admin();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();

        $viewerroleid = $this->getDataGenerator()->create_role();
        assign_capability('enrol/programs:view', CAP_ALLOW, $viewerroleid, $syscontext);
        role_assign($viewerroleid, $user1->id, $syscontext->id);
        role_assign($viewerroleid, $user2->id, $catcontext1->id);

        $program1 = $generator->create_program([
            'fullname' => 'hokus',
            'idnumber' => 'p1',
            'description' => 'some desc 1',
            'descriptionformat' => FORMAT_MARKDOWN,
            'public' => 1,
            'archived' => 0,
            'contextid' => $syscontext->id,
            'sources' => ['manual' => []],
            'cohorts' => [$cohort1->id],
        ]);
        $program2 = $generator->create_program([
            'fullname' => 'pokus',
            'idnumber' => 'p2',
            'description' => '<b>some desc 2</b>',
            'descriptionformat' => FORMAT_HTML,
            'public' => 0,
            'archived' => 0,
            'contextid' => $catcontext1->id,
            'sources' => ['manual' => [], 'cohort' => []],
            'cohorts' => [$cohort1->id, $cohort2->id],
        ]);
        $program3 = $generator->create_program([
            'fullname' => 'Prog3',
            'idnumber' => 'p3',
            'public' => 1,
            'archived' => 1,
            'contextid' => $syscontext->id,
            'sources' => ['manual' => []]
        ]);

        $this->setUser($admin);
        $response = get_programs::execute([]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(3, $results);

        $result = $results[0];
        $this->assertIsArray($result);
        $result = (object)$result;
        $this->assertSame((int)$program1->id, $result->id);
        $this->assertSame((int)$program1->contextid, $result->contextid);
        $this->assertSame($program1->fullname, $result->fullname);
        $this->assertSame($program1->idnumber, $result->idnumber);
        $this->assertSame($program1->description, $result->description);
        $this->assertSame((int)$program1->descriptionformat, $result->descriptionformat);
        $this->assertSame('[]', $result->presentationjson);
        $this->assertSame(true, $result->public);
        $this->assertSame(false, $result->archived);
        $this->assertSame(false, $result->creategroups);
        $this->assertSame(null, $result->timeallocationstart);
        $this->assertSame(null, $result->timeallocationend);
        $this->assertSame('{"type":"allocation"}', $result->startdatejson);
        $this->assertSame('{"type":"notset"}', $result->duedatejson);
        $this->assertSame('{"type":"notset"}', $result->enddatejson);
        $this->assertSame((int)$program1->timecreated, $result->timecreated);
        $this->assertSame(['manual'], $result->sources);
        $this->assertSame([], $result->cohortids);

        $result = $results[1];
        $this->assertIsArray($result);
        $result = (object)$result;
        $this->assertSame((int)$program2->id, $result->id);
        $this->assertSame((int)$catcontext1->id, $result->contextid);
        $this->assertSame($program2->fullname, $result->fullname);
        $this->assertSame($program2->idnumber, $result->idnumber);
        $this->assertSame($program2->description, $result->description);
        $this->assertSame((int)$program2->descriptionformat, $result->descriptionformat);
        $this->assertSame('[]', $result->presentationjson);
        $this->assertSame(false, $result->public);
        $this->assertSame(false, $result->archived);
        $this->assertSame(false, $result->creategroups);
        $this->assertSame(null, $result->timeallocationstart);
        $this->assertSame(null, $result->timeallocationend);
        $this->assertSame('{"type":"allocation"}', $result->startdatejson);
        $this->assertSame('{"type":"notset"}', $result->duedatejson);
        $this->assertSame('{"type":"notset"}', $result->enddatejson);
        $this->assertSame((int)$program2->timecreated, $result->timecreated);
        $this->assertSame(['cohort', 'manual'], $result->sources);
        $this->assertSame([(int)$cohort1->id, (int)$cohort2->id], $result->cohortids);

        $result = $results[2];
        $this->assertIsArray($result);
        $result = (object)$result;
        $this->assertSame((int)$program3->id, $result->id);
        $this->assertSame((int)$program3->contextid, $result->contextid);
        $this->assertSame($program3->fullname, $result->fullname);
        $this->assertSame($program3->idnumber, $result->idnumber);
        $this->assertSame($program3->description, $result->description);
        $this->assertSame((int)$program3->descriptionformat, $result->descriptionformat);
        $this->assertSame('[]', $result->presentationjson);
        $this->assertSame(true, $result->public);
        $this->assertSame(true, $result->archived);
        $this->assertSame(false, $result->creategroups);
        $this->assertSame(null, $result->timeallocationstart);
        $this->assertSame(null, $result->timeallocationend);
        $this->assertSame('{"type":"allocation"}', $result->startdatejson);
        $this->assertSame('{"type":"notset"}', $result->duedatejson);
        $this->assertSame('{"type":"notset"}', $result->enddatejson);
        $this->assertSame((int)$program3->timecreated, $result->timecreated);
        $this->assertSame(['manual'], $result->sources);
        $this->assertSame([], $result->cohortids);

        $response = get_programs::execute([['field' => 'id', 'value' => $program1->id]]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(1, $results);
        $this->assertEquals($program1->id, $results[0]['id']);

        $response = get_programs::execute([['field' => 'idnumber', 'value' => $program1->idnumber]]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(1, $results);
        $this->assertEquals($program1->id, $results[0]['id']);

        $response = get_programs::execute([['field' => 'fullname', 'value' => $program1->fullname]]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(1, $results);
        $this->assertEquals($program1->id, $results[0]['id']);

        $response = get_programs::execute([['field' => 'contextid', 'value' => $syscontext->id]]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(2, $results);
        $this->assertEquals($program1->id, $results[0]['id']);
        $this->assertEquals($program3->id, $results[1]['id']);

        $response = get_programs::execute([['field' => 'public', 'value' => 1]]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(2, $results);
        $this->assertEquals($program1->id, $results[0]['id']);
        $this->assertEquals($program3->id, $results[1]['id']);

        $response = get_programs::execute([['field' => 'archived', 'value' => 0]]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(2, $results);
        $this->assertEquals($program1->id, $results[0]['id']);
        $this->assertEquals($program2->id, $results[1]['id']);

        $response = get_programs::execute([['field' => 'id', 'value' => $program1->id], ['field' => 'public', 'value' => 1]]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(1, $results);
        $this->assertEquals($program1->id, $results[0]['id']);

        $response = get_programs::execute([['field' => 'id', 'value' => $program1->id], ['field' => 'public', 'value' => true]]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(1, $results);
        $this->assertEquals($program1->id, $results[0]['id']);

        $response = get_programs::execute([['field' => 'id', 'value' => $program1->id], ['field' => 'public', 'value' => 0]]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(0, $results);

        $response = get_programs::execute([['field' => 'id', 'value' => $program1->id], ['field' => 'public', 'value' => false]]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(0, $results);

        $this->setUser($user1);
        $response = get_programs::execute([]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(3, $results);
        $this->assertEquals($program1->id, $results[0]['id']);
        $this->assertEquals($program2->id, $results[1]['id']);
        $this->assertEquals($program3->id, $results[2]['id']);

        $this->setUser($user2);
        $response = get_programs::execute([]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(1, $results);
        $this->assertEquals($program2->id, $results[0]['id']);

        $this->setUser($user3);
        $response = get_programs::execute([]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(0, $results);

        $this->setUser($admin);
        try {
            get_programs::execute([['field' => 'arar', 'value' => 'hokus']]);
            $this->fail('Exception expected');
        } catch (\moodle_exception $ex) {
            $this->assertInstanceOf(\invalid_parameter_exception::class, $ex);
            $this->assertSame('Invalid parameter value detected (Invalid field name: arar)', $ex->getMessage());
        }
        try {
            get_programs::execute([['field' => 'id', 'value' => null]]);
            $this->fail('Exception expected');
        } catch (\moodle_exception $ex) {
            $this->assertInstanceOf(\invalid_parameter_exception::class, $ex);
            $this->assertSame('Invalid parameter value detected (Field value cannot be NULL: id)', $ex->getMessage());
        }
        try {
            get_programs::execute([['field' => 'id', 'value' => 1], ['field' => 'id', 'value' => 2]]);
            $this->fail('Exception expected');
        } catch (\moodle_exception $ex) {
            $this->assertInstanceOf(\invalid_parameter_exception::class, $ex);
            $this->assertSame('Invalid parameter value detected (Invalid duplicate field name: id)', $ex->getMessage());
        }
    }

    public function test_execute_tenants() {
        if (!\enrol_programs\local\tenant::is_available()) {
            $this->markTestSkipped('tenant support not available');
        }

        \tool_olms_tenant\tenants::activate_tenants();

        /** @var \tool_olms_tenant_generator $generator */
        $tenantgenerator = $this->getDataGenerator()->get_plugin_generator('tool_olms_tenant');
        /** @var \enrol_programs_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('enrol_programs');

        $tenant1 = $tenantgenerator->create_tenant();
        $tenantcontext1 = \context_coursecat::instance($tenant1->categoryid);

        $tenant2 = $tenantgenerator->create_tenant();
        $tenantcontext2 = \context_coursecat::instance($tenant2->categoryid);
        $tenantsubcategory2 = $this->getDataGenerator()->create_category(['parent' => $tenant2->categoryid]);
        $tenantsubcontext2 = \context_coursecat::instance($tenantsubcategory2->id);

        $program0 = $generator->create_program([
            'fullname' => 'Prog 0',
            'sources' => ['manual' => []]
        ]);
        $program1 = $generator->create_program([
            'fullname' => 'Prog 1',
            'contextid' => $tenantcontext1->id,
            'sources' => ['manual' => []]
        ]);
        $program2 = $generator->create_program([
            'fullname' => 'Prog 2',
            'public' => 1,
            'contextid' => $tenantcontext2->id,
            'sources' => ['manual' => []]
        ]);
        $program3 = $generator->create_program([
            'fullname' => 'Prog 3',
            'public' => 0,
            'contextid' => $tenantsubcontext2->id,
            'sources' => ['manual' => []]
        ]);

        $this->setAdminUser();

        $response = get_programs::execute([]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(4, $results);

        $response = get_programs::execute([['field' => 'tenantid', 'value' => null]]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(1, $results);
        $this->assertEquals($program0->id, $results[0]['id']);

        $response = get_programs::execute([['field' => 'tenantid', 'value' => $tenant1->id]]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(1, $results);
        $this->assertEquals($program1->id, $results[0]['id']);

        $response = get_programs::execute([['field' => 'tenantid', 'value' => $tenant2->id]]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(2, $results);
        $this->assertEquals($program2->id, $results[0]['id']);
        $this->assertEquals($program3->id, $results[1]['id']);

        $response = get_programs::execute([['field' => 'tenantid', 'value' => $tenant2->id], ['field' => 'public', 'value' => 1]]);
        $results = get_programs::clean_returnvalue(get_programs::execute_returns(), $response);
        $this->assertCount(1, $results);
        $this->assertEquals($program2->id, $results[0]['id']);
    }
}
