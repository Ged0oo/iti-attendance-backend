<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $trackCourses = [
            'Open Source Application Development' => [
                ['Linux System Administration',    'Shell scripting, user management, permissions, cron jobs'],
                ['Python Programming',             'OOP, modules, file I/O, virtual environments, packaging'],
                ['Django Web Framework',           'MVT, ORM, DRF, authentication, deployment'],
                ['PostgreSQL & Database Design',   'Normalization, indexing, transactions, stored procedures'],
                ['Open Source ERP with Odoo',      'Module development, XML views, ORM, business workflows'],
            ],
            'Artificial Intelligence & Machine Learning' => [
                ['Mathematics for ML',             'Linear algebra, calculus, probability, statistics'],
                ['Python for Data Science',        'NumPy, Pandas, Matplotlib, Seaborn, data wrangling'],
                ['Machine Learning Fundamentals',  'Supervised/unsupervised learning, scikit-learn, evaluation'],
                ['Deep Learning with TensorFlow',  'CNNs, RNNs, transfer learning, Keras, model optimization'],
                ['Natural Language Processing',    'Tokenization, embeddings, transformers, BERT fine-tuning'],
            ],
            'Embedded Systems & IoT' => [
                ['C for Embedded Systems',         'Pointers, memory management, bit manipulation, MISRA C'],
                ['Microcontroller Architectures',  'ARM Cortex-M, GPIO, timers, interrupts, STM32 HAL'],
                ['Real-Time Operating Systems',    'FreeRTOS tasks, queues, semaphores, priority scheduling'],
                ['IoT Protocols & Communication',  'MQTT, I2C, SPI, UART, BLE, LoRa, CoAP'],
                ['PCB Design & Hardware',          'KiCad, sensors, actuators, power management, debugging'],
            ],
            'Cybersecurity' => [
                ['Networking & Protocols',         'TCP/IP, DNS, HTTP/S, VPNs, firewalls, Wireshark analysis'],
                ['Ethical Hacking & Pentesting',   'Kali Linux, Metasploit, Burp Suite, OWASP Top 10'],
                ['Web Application Security',       'SQLi, XSS, CSRF, authentication flaws, secure coding'],
                ['Security Operations & SIEM',     'Log analysis, threat detection, Splunk, incident response'],
                ['Digital Forensics',              'Evidence acquisition, memory forensics, Autopsy, reporting'],
            ],
            'UI/UX Design' => [
                ['Design Principles & Theory',         'Color theory, typography, Gestalt principles, accessibility'],
                ['User Research & Analysis',            'Interviews, personas, empathy maps, user journey mapping'],
                ['Wireframing & Prototyping',           'Low/high-fi wireframes, interactive prototypes in Figma'],
                ['Usability Testing',                   'Test planning, moderated sessions, heuristic evaluation'],
                ['Motion Design & Micro-interactions',  'After Effects, Lottie, animation principles for UI'],
            ],
            'Full Stack Web Development' => [
                ['HTML, CSS & Responsive Design',  'Flexbox, Grid, Bootstrap, accessibility, cross-browser'],
                ['JavaScript & TypeScript',        'ES6+, async/await, DOM, TypeScript types and generics'],
                ['React.js',                       'Components, hooks, Context API, React Router, performance'],
                ['Node.js & Express',              'REST API design, middleware, JWT auth, file uploads'],
                ['Databases & DevOps',             'MySQL, MongoDB, Redis, Docker, CI/CD, cloud deployment'],
            ],
            'Business Intelligence & Data Engineering' => [
                ['Advanced SQL',                'Window functions, CTEs, query optimization, indexing'],
                ['Data Warehousing',            'Star/snowflake schema, fact tables, OLAP, Kimball methodology'],
                ['ETL Pipeline Development',    'Apache Airflow, Talend, data transformation, scheduling'],
                ['Power BI & Visualization',    'DAX, data modeling, dashboards, reports, Power Query'],
                ['Big Data Fundamentals',       'Hadoop, Spark, HDFS, batch vs stream processing, Kafka'],
            ],
            'Mobile Application Development' => [
                ['Mobile UI Fundamentals',          'HIG, Material Design, responsive layouts, platform guidelines'],
                ['Flutter & Dart',                  'Widgets, state management (BLoC/Provider), navigation'],
                ['Android Development (Kotlin)',    'Jetpack Compose, ViewModel, Room, Retrofit, Coroutines'],
                ['iOS Development (Swift)',         'SwiftUI, UIKit, Core Data, networking, App Store submission'],
                ['Backend Integration & DevOps',   'REST APIs, Firebase, push notifications, CI/CD for mobile'],
            ],
        ];

        $created = 0;

        foreach ($trackCourses as $trackName => $courses) {
            // Only seed courses for Intake 46 (delivering) cohorts
            $trackId = DB::table('tracks')->where('name', $trackName)->value('id');
            if (!$trackId) {
                $this->command->warn("  Track '{$trackName}' not found — skipping.");
                continue;
            }

            $cohortId = DB::table('cohorts')
                ->where('track_id', $trackId)
                ->where('name', 'Intake 46')
                ->where('status', 'delivering')
                ->value('id');

            if (!$cohortId) {
                $this->command->warn("  Intake 46 (delivering) not found for track '{$trackName}' — skipping.");
                continue;
            }

            foreach ($courses as [$courseName, $description]) {
                $exists = DB::table('courses')
                    ->where('cohort_id', $cohortId)
                    ->where('name', $courseName)
                    ->exists();

                if (!$exists) {
                    DB::table('courses')->insert([
                        'cohort_id'   => $cohortId,
                        'name'        => $courseName,
                        'description' => $description,
                        'max_score'   => 100,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $created++;
                }
            }
        }

        $total = DB::table('courses')->count();
        $this->command->info("✔ CourseSeeder — {$total} courses present ({$created} new). Only Intake 46 cohorts.");
    }
}
