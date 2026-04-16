<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TestScript;
use App\Models\TestStep;
use Illuminate\Database\Seeder;

/**
 * Seeds a library of NexusOps system-owned Functional Performance Test
 * scripts. These are visible to every tenant and read-only.
 *
 * The scripts are designed after standard mission-critical commissioning
 * practice (ASHRAE Guideline 0, NEBB procedural guides, data-center L3/L4
 * integrated systems tests) so a commissioning authority would recognize
 * them on sight.
 */
class FptScriptSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->scriptDefinitions() as $def) {
            $script = TestScript::updateOrCreate(
                [
                    'slug' => $def['slug'],
                    'is_system' => true,
                    'version' => 1,
                ],
                [
                    'tenant_id' => null,
                    'created_by' => null,
                    'name' => $def['name'],
                    'description' => $def['description'],
                    'system_type' => $def['system_type'],
                    'cx_level' => $def['cx_level'] ?? null,
                    'status' => TestScript::STATUS_PUBLISHED,
                    'is_system' => true,
                    'estimated_duration_minutes' => $def['duration'],
                ],
            );

            $script->steps()->delete();

            foreach ($def['steps'] as $i => $step) {
                TestStep::create(array_merge([
                    'test_script_id' => $script->id,
                    'sequence' => $i + 1,
                ], $step));
            }
        }
    }

    /**
     * @return array<int, array{slug:string, name:string, description:string, system_type:string, duration:int, steps: array<int, array<string, mixed>>}>
     */
    private function scriptDefinitions(): array
    {
        return [
            // ─── Chiller L3 FPT ───────────────────────────────────────────
            [
                'slug' => 'chiller-l3-fpt',
                'name' => 'Chiller L3 Functional Performance Test',
                'description' => 'Level 3 component-level FPT for centrifugal chillers — verifies start-up, loading, safeties, and BMS integration against design intent.',
                'system_type' => 'chiller',
                'cx_level' => 'L3',
                'duration' => 90,
                'steps' => [
                    [
                        'title' => 'Pre-start inspection complete',
                        'instruction' => 'Confirm chiller is isolated, refrigerant charge is documented, and all pre-start items on OEM checklist are complete.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                        'is_critical' => true,
                    ],
                    [
                        'title' => 'Supply water temperature at design',
                        'instruction' => 'Allow chiller to stabilize at full load for 15 minutes. Record chilled water supply temperature.',
                        'measurement_type' => 'numeric',
                        'expected_value' => '44 °F',
                        'expected_numeric' => 44,
                        'tolerance' => 1.5,
                        'measurement_unit' => '°F',
                        'sensor_metric_key' => 'temperature',
                        'is_critical' => true,
                        'auto_evaluate' => true,
                        'evaluation_mode' => 'within_tolerance',
                    ],
                    [
                        'title' => 'Chilled water delta-T within spec',
                        'instruction' => 'Record chilled water supply and return temperatures. Verify delta-T is within 8–12 °F design window.',
                        'measurement_type' => 'numeric',
                        'expected_value' => '10 °F',
                        'expected_numeric' => 10,
                        'tolerance' => 2,
                        'measurement_unit' => '°F',
                        'auto_evaluate' => true,
                        'evaluation_mode' => 'between',
                        'acceptable_min' => 8,
                        'acceptable_max' => 12,
                    ],
                    [
                        'title' => 'Compressor vibration within tolerance',
                        'instruction' => 'Using handheld vibration analyzer, measure compressor bearing vibration in mm/s RMS.',
                        'measurement_type' => 'numeric',
                        'expected_value' => '< 4.5 mm/s',
                        'expected_numeric' => 2.5,
                        'tolerance' => 2,
                        'measurement_unit' => 'mm/s',
                        'sensor_metric_key' => 'vibration',
                        'is_critical' => true,
                        'auto_evaluate' => true,
                        'evaluation_mode' => 'less_than_or_equal',
                        'acceptable_max' => 4.5,
                    ],
                    [
                        'title' => 'Low evaporator temperature cutout',
                        'instruction' => 'Simulate loss of chilled water flow and verify chiller trips on low evaporator temperature safety.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                        'is_critical' => true,
                        'requires_witness' => true,
                    ],
                    [
                        'title' => 'High condenser pressure cutout',
                        'instruction' => 'Reduce condenser water flow to simulate high head pressure. Verify safety trip and annunciation.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                        'is_critical' => true,
                    ],
                    [
                        'title' => 'BMS integration verification',
                        'instruction' => 'Confirm chiller status, supply/return temps, kW, and alarms are trending correctly in the BMS.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                    ],
                    [
                        'title' => 'Sequence of Operations deviations',
                        'instruction' => 'Document any deviations from the design Sequence of Operations observed during this test.',
                        'measurement_type' => 'text',
                    ],
                ],
            ],

            // ─── UPS L3 FPT ───────────────────────────────────────────────
            [
                'slug' => 'ups-l3-fpt',
                'name' => 'UPS L3 Functional Performance Test',
                'description' => 'Level 3 FPT for double-conversion UPS modules. Validates input/output, battery autonomy, transfer behavior, and alarms.',
                'system_type' => 'ups',
                'cx_level' => 'L3',
                'duration' => 60,
                'steps' => [
                    [
                        'title' => 'Input voltage within tolerance',
                        'instruction' => 'Measure input voltage on all three phases under normal load.',
                        'measurement_type' => 'numeric',
                        'expected_value' => '480 V ±5%',
                        'expected_numeric' => 480,
                        'tolerance' => 24,
                        'measurement_unit' => 'V',
                        'is_critical' => true,
                        'auto_evaluate' => true,
                        'evaluation_mode' => 'within_tolerance',
                    ],
                    [
                        'title' => 'Output voltage regulation',
                        'instruction' => 'Verify output voltage regulation under step load changes of 25%, 50%, 75%, 100%.',
                        'measurement_type' => 'numeric',
                        'expected_value' => '480 V ±1%',
                        'expected_numeric' => 480,
                        'tolerance' => 4.8,
                        'measurement_unit' => 'V',
                        'is_critical' => true,
                        'auto_evaluate' => true,
                        'evaluation_mode' => 'within_tolerance',
                    ],
                    [
                        'title' => 'Output THD at full load',
                        'instruction' => 'Record total harmonic distortion on the output at 100% load.',
                        'measurement_type' => 'numeric',
                        'expected_value' => '< 5 %',
                        'expected_numeric' => 2.5,
                        'tolerance' => 2.5,
                        'measurement_unit' => '%',
                    ],
                    [
                        'title' => 'Battery autonomy test',
                        'instruction' => 'Disconnect input utility. Verify UPS maintains load for the specified battery autonomy time.',
                        'measurement_type' => 'numeric',
                        'expected_value' => '≥ 15 min',
                        'expected_numeric' => 15,
                        'tolerance' => 0,
                        'measurement_unit' => 'min',
                        'is_critical' => true,
                        'requires_witness' => true,
                        'auto_evaluate' => true,
                        'evaluation_mode' => 'greater_than_or_equal',
                        'acceptable_min' => 15,
                    ],
                    [
                        'title' => 'Maintenance bypass transfer',
                        'instruction' => 'Transfer the UPS to maintenance bypass and back to normal. Verify no-break transfer and alarm behavior.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                        'is_critical' => true,
                    ],
                    [
                        'title' => 'All alarms and notifications verified',
                        'instruction' => 'Simulate at least three alarm conditions (low battery, overload, bypass active) and confirm BMS + SNMP notifications fire correctly.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                    ],
                    [
                        'title' => 'SOO deviations',
                        'instruction' => 'Document any deviations from the approved Sequence of Operations.',
                        'measurement_type' => 'text',
                    ],
                ],
            ],

            // ─── Generator L4 FPT ─────────────────────────────────────────
            [
                'slug' => 'generator-l4-fpt',
                'name' => 'Generator L4 Integrated Systems Test',
                'description' => 'Level 4 integrated systems test for standby generator + ATS — verifies load-pickup time, parallel operation, and fuel system behavior.',
                'system_type' => 'generator',
                'cx_level' => 'L4',
                'duration' => 120,
                'steps' => [
                    [
                        'title' => 'Cold start — start time',
                        'instruction' => 'From a stable cold condition, drop utility. Record time from outage to generator online and loaded.',
                        'measurement_type' => 'numeric',
                        'expected_value' => '≤ 10 s',
                        'expected_numeric' => 8,
                        'tolerance' => 2,
                        'measurement_unit' => 's',
                        'is_critical' => true,
                        'requires_witness' => true,
                        'auto_evaluate' => true,
                        'evaluation_mode' => 'less_than_or_equal',
                        'acceptable_max' => 10,
                    ],
                    [
                        'title' => 'Voltage & frequency on pickup',
                        'instruction' => 'Once loaded, measure voltage and frequency at the generator output breaker.',
                        'measurement_type' => 'numeric',
                        'expected_value' => '480 V / 60 Hz',
                        'expected_numeric' => 480,
                        'tolerance' => 5,
                        'measurement_unit' => 'V',
                        'is_critical' => true,
                    ],
                    [
                        'title' => 'Block load pickup',
                        'instruction' => 'Apply design block load (≥ 80 % of nameplate) and verify voltage dip/recovery is within spec.',
                        'measurement_type' => 'numeric',
                        'expected_value' => 'Dip < 20 %',
                        'expected_numeric' => 10,
                        'tolerance' => 10,
                        'measurement_unit' => '%',
                        'is_critical' => true,
                    ],
                    [
                        'title' => 'ATS re-transfer on utility return',
                        'instruction' => 'Restore utility. Confirm ATS re-transfer delay, cool-down cycle, and generator shutdown sequence.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                        'is_critical' => true,
                    ],
                    [
                        'title' => 'Fuel level & transfer pump operation',
                        'instruction' => 'Verify day-tank level and confirm fuel transfer pumps operate at the correct set points.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                    ],
                    [
                        'title' => 'BMS / EPMS integration',
                        'instruction' => 'Confirm generator status, kW, fuel level, and alarms are trending in the monitoring system.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                    ],
                ],
            ],

            // ─── CRAH / CRAC Unit FPT ─────────────────────────────────────
            [
                'slug' => 'crah-l3-fpt',
                'name' => 'CRAH / CRAC L3 Functional Performance Test',
                'description' => 'Level 3 FPT for Computer Room Air Handlers — supply temp control, EC fan modulation, valve position, and BMS integration.',
                'system_type' => 'crac',
                'cx_level' => 'L3',
                'duration' => 45,
                'steps' => [
                    [
                        'title' => 'Supply air temperature on setpoint',
                        'instruction' => 'With unit running for 15 minutes at stable load, record the supply air temperature.',
                        'measurement_type' => 'numeric',
                        'expected_value' => '68 °F',
                        'expected_numeric' => 68,
                        'tolerance' => 2,
                        'measurement_unit' => '°F',
                        'sensor_metric_key' => 'temperature',
                        'is_critical' => true,
                        'auto_evaluate' => true,
                        'evaluation_mode' => 'within_tolerance',
                    ],
                    [
                        'title' => 'Return air temperature recorded',
                        'instruction' => 'Record return air temperature and verify delta-T with supply is within design.',
                        'measurement_type' => 'numeric',
                        'expected_value' => '78 °F',
                        'expected_numeric' => 78,
                        'tolerance' => 4,
                        'measurement_unit' => '°F',
                    ],
                    [
                        'title' => 'Room humidity on setpoint',
                        'instruction' => 'Verify room humidity is within 40–60 % RH band.',
                        'measurement_type' => 'numeric',
                        'expected_value' => '50 %RH',
                        'expected_numeric' => 50,
                        'tolerance' => 10,
                        'measurement_unit' => '%RH',
                        'sensor_metric_key' => 'humidity',
                    ],
                    [
                        'title' => 'EC fan modulation',
                        'instruction' => 'Adjust setpoint to force fan modulation. Verify fan speed tracks pressure / temp control signal smoothly.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                    ],
                    [
                        'title' => 'Chilled water valve stroke',
                        'instruction' => 'Observe chilled water valve stroking 0–100 % under control; confirm no hunting.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                    ],
                    [
                        'title' => 'BMS trend verification',
                        'instruction' => 'Confirm supply temp, return temp, fan speed, and valve % are trending in the BMS.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                    ],
                ],
            ],

            // ─── ATS FPT ──────────────────────────────────────────────────
            [
                'slug' => 'ats-l3-fpt',
                'name' => 'ATS L3 Functional Performance Test',
                'description' => 'Level 3 FPT for Automatic Transfer Switches — utility loss, retransfer, time-delay behaviour, and alarm annunciation.',
                'system_type' => 'ats',
                'cx_level' => 'L3',
                'duration' => 45,
                'steps' => [
                    [
                        'title' => 'Source 1 → Source 2 transfer time',
                        'instruction' => 'Drop Source 1 and record transfer time to Source 2.',
                        'measurement_type' => 'numeric',
                        'expected_value' => '≤ 6 s',
                        'expected_numeric' => 4,
                        'tolerance' => 2,
                        'measurement_unit' => 's',
                        'is_critical' => true,
                        'auto_evaluate' => true,
                        'evaluation_mode' => 'less_than_or_equal',
                        'acceptable_max' => 6,
                    ],
                    [
                        'title' => 'Time-delay emergency (TDE)',
                        'instruction' => 'Confirm TDE timer operates per setpoint before transfer initiates.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                    ],
                    [
                        'title' => 'Re-transfer time delay (TDN)',
                        'instruction' => 'Restore Source 1 and verify TDN time delay before re-transfer to normal.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                        'is_critical' => true,
                    ],
                    [
                        'title' => 'Load-side voltage continuity',
                        'instruction' => 'Confirm load voltage never drops below the hold-up spec during transfer (via UPS bridging).',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                        'is_critical' => true,
                        'requires_witness' => true,
                    ],
                    [
                        'title' => 'BMS alarm verification',
                        'instruction' => 'Confirm ATS position and alarms appear correctly in the BMS / EPMS.',
                        'measurement_type' => 'boolean',
                        'expected_value' => 'Yes',
                    ],
                ],
            ],
        ];
    }
}
