<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate the tables to clear existing data
        DB::table('isea_setting')->truncate();
        DB::table('isea_setting_de')->truncate();

        // Services array
        $services = [
            [
                "title" => "Easy Application Process",
                "status" => 1,
                "order" => 1,
                "type" => 'Interns',
                "setting_type" => "OurService",
                "itemDe" => [
                    [
                        "title" => "Simple Online Booking",
                        "description" => "Our user-friendly website allows you to browse through a diverse range of internship opportunities and apply with ease. You can search by industry, company, and location to find the perfect match for your skills and interests.",
                        "status" => 1,
                    ],
                    [
                        "title" => "Optional Consultation",
                        "description" => "To ensure the best fit, we offer an optional consultation with a British national residing in Cambodia. This personalized session helps you understand the specifics of the internship, cultural expectations, and daily life in Cambodia.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Pre-Departure Support",
                "status" => 1,
                "order" => 2,
                "type" => 'Interns',
                "setting_type" => "OurService",
                "itemDe" => [
                    [
                        "title" => "Flight and Visa Arrangements",
                        "description" => "We assist you with booking flights and handling visa applications, ensuring all necessary documents are in order.",
                        "status" => 1,
                    ],
                    [
                        "title" => "Pre-Departure Orientation",
                        "description" => "We provide detailed pre-departure information, including cultural insights, packing tips, and travel advice to prepare you for your journey.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Post-Arrival Assistance",
                "status" => 1,
                "order" => 3,
                "type" => 'Interns',
                "setting_type" => "OurService",
                "itemDe" => [
                    [
                        "title" => "Airport Pickup",
                        "description" => "Upon arrival in Cambodia, you will be greeted by our team and provided with transportation to your accommodation.",
                        "status" => 1,
                    ],
                    [
                        "title" => "Accommodation Arrangements",
                        "description" => "We offer a range of housing options to suit different preferences and budgets, ensuring you have a comfortable place to stay.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "In-Country Support",
                "status" => 1,
                "order" => 4,
                "type" => 'Interns',
                "setting_type" => "OurService",
                "itemDe" => [
                    [
                        "title" => "Orientation Program",
                        "description" => "We conduct a comprehensive orientation program to help you acclimate to your new environment. This includes language basics, cultural norms, and practical information about living in Cambodia.",
                        "status" => 1,
                    ],
                    [
                        "title" => "24/7 Support",
                        "description" => "Our team is available around the clock to provide assistance and address any concerns you may have during your stay.",
                        "status" => 1,
                    ],
                    [
                        "title" => "Networking Opportunities",
                        "description" => "We organize events and activities to help you network with other interns, professionals, and local communities, enhancing your overall experience.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Post-Internship Support",
                "status" => 1,
                "order" => 5,
                "type" => 'Interns',
                "setting_type" => "OurService",
                "itemDe" => [
                    [
                        "title" => "Career Guidance",
                        "description" => "We offer career counseling and support to help you leverage your internship experience in your future career.",
                        "status" => 1,
                    ],
                    [
                        "title" => "Certificate of Completion",
                        "description" => "Upon successful completion of your internship, you will receive a certificate recognizing your achievements and contributions.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Partnership Development",
                "status" => 1,
                "order" => 1,
                "type" => 'Providers',
                "setting_type" => "OurService",
                "itemDe" => [
                    [
                        "title" => "Consultation Services",
                        "description" => "We work closely with companies to identify and develop valuable internship opportunities. Our team provides expert advice on creating positions that are beneficial for both the intern and the organization.",
                        "status" => 1,
                    ],
                    [
                        "title" => "Tailored Solutions",
                        "description" => "We understand that each company has unique needs. Our tailored solutions ensure that the internships we help create align with your business goals and requirements.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Recruitment and Selection",
                "status" => 1,
                "order" => 2,
                "type" => 'Providers',
                "setting_type" => "OurService",
                "itemDe" => [
                    [
                        "title" => "Vetting Process",
                        "description" => "We conduct a thorough vetting process to ensure that interns are well-suited for the positions offered, matching their skills and interests with your organizational needs.",
                        "status" => 1,
                    ],
                    [
                        "title" => "Intern Matching",
                        "description" => "Our sophisticated matching system ensures that you receive candidates who are not only qualified but also passionate about the opportunity.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Ongoing Support",
                "status" => 1,
                "order" => 3,
                "type" => 'Providers',
                "setting_type" => "OurService",
                "itemDe" => [
                    [
                        "title" => "Intern Management",
                        "description" => "We provide support in managing interns, including setting goals, monitoring progress, and addressing any issues that may arise.",
                        "status" => 1,
                    ],
                    [
                        "title" => "Feedback Mechanisms",
                        "description" => "Regular feedback sessions help ensure that the internship experience is positive for both the intern and the organization.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Economic and Human Capital Boost",
                "status" => 1,
                "order" => 1,
                "type" => 'Vision',
                "setting_type" => "OurService",
                "itemDe" => [
                    [
                        "title" => "Increasing Visitor Numbers",
                        "description" => "By attracting UK citizens to Cambodia, we contribute to the tourism sector and boost local businesses.",
                        "status" => 1,
                    ],
                    [
                        "title" => "Revenue Generation",
                        "description" => "Interns bring new revenue streams, benefiting local economies and fostering sustainable growth.",
                        "status" => 1,
                    ],
                    [
                        "title" => "Skill Enhancement",
                        "description" => "Our internships help develop the skills and capacities of both interns and local employees, enhancing the overall human capital of Cambodia.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Strengthening International Ties",
                "status" => 1,
                "order" => 1,
                "type" => 'Vision',
                "setting_type" => "OurService",
                "itemDe" => [
                    [
                        "title" => "Cultural Exchange",
                        "description" => "Our programs pro-mote cultural exchange, fostering greater un-derstanding and cooperation between the UK and Cambodia.",
                        "status" => 1,
                    ],
                    [
                        "title" => "Institutional Collaboration",
                        "description" => "We work closely with the British Embassy, British Cham-ber of Commerce, and local educational insti-tutions to enhance the professional land-scape in Cambodia.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Positive Community Impact",
                "status" => 1,
                "order" => 1,
                "type" => 'Vision',
                "setting_type" => "OurService",
                "itemDe" => [
                    [
                        "title" => "Local Engagement",
                        "description" => "Our interns often par-ticipate in community projects, contributing to the social and economic development of local communities.",
                        "status" => 1,
                    ],
                    [
                        "title" => "Sustainability Initiatives",
                        "description" => "We support sustainable practices and initiatives that bene-fit both the environment and the local popula-tion.",
                        "status" => 1,
                    ]
                ]
            ],

            //About
            [
                "title" => "Our Mission",
                "status" => 1,
                "order" => 1,
                "type" => 'AboutMission',
                "setting_type" => "About",
                "itemDe" => [
                    [
                        "title" => "Our Mission",
                        "description" => "At Internship SEA (ISEA), our mission is to bridge the gap between the increasing demand for international internships among UK citizens and the growing need for skilled professionals in South East Asia. We aim to provide exceptional internship experiences that are mutually beneficial for interns and host organizations, contributing to the growth and development of Cambodia.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Our Vision",
                "status" => 1,
                "order" => 2, // Updated order
                "type" => 'AboutMission',
                "setting_type" => "About",
                "itemDe" => [
                    [
                        "title" => "Our Vision",
                        "description" => "We envision a world where young professionals from the UK can gain invaluable international experience while contributing positively to the host country's development. By creating a platform that supports both interns and providers, we strive to be the leading facilitator of impactful internships in South East Asia, setting a benchmark for quality and service in the international internship sector.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Tom Starkey",
                "status" => 1,
                "order" => 1, // Reset order for new type
                "type" => 'Founders',
                "setting_type" => "About",
                "itemDe" => [
                    [
                        "title" => "Tom Starkey",
                        "description" => "With over 15 years of international experience, Tom Starkey has a proven track record in managing development and private sector projects with interns and volunteers. He has co-founded the HR platform Next Step and managed eco-tourism projects. As the Managing Director of ISEA, Tom leads partnerships with the British Embassy and British Chamber of Commerce, oversees marketing strategies, and facilitates the entire internship process. His deep connection with Cambodia is reflected in his Cambodia Lifestyle platform, which promotes the best of Cambodian experiences.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Veronica Pou",
                "status" => 1,
                "order" => 2, // Updated order
                "type" => 'Founders',
                "setting_type" => "About",
                "itemDe" => [
                    [
                        "title" => "Veronica Pou",
                        "description" => "As an HR specialist for a leading Cambodian firm, Veronica Pou brings a deep understanding of the Cambodian market and extensive connections within the recruitment landscape. She serves as the Chief Financial Operator, responsible for initial capital investment and building the financial strategy for ISEA in its first three years.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Excellence",
                "status" => 1,
                "order" => 1, // Reset order for new type
                "type" => 'Values',
                "setting_type" => "About",
                "itemDe" => [
                    [
                        "title" => "Excellence",
                        "description" => "We strive to provide the highest quality of service to our interns and partner organizations, ensuring that every internship experience is enriching and valuable.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Integrity",
                "status" => 1,
                "order" => 2, // Updated order
                "type" => 'Values',
                "setting_type" => "About",
                "itemDe" => [
                    [
                        "title" => "Integrity",
                        "description" => "We conduct our operations with transparency and honesty, building trust with our interns, partners, and the communities we serve.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Collaboration",
                "status" => 1,
                "order" => 3, // Updated order
                "type" => 'Values',
                "setting_type" => "About",
                "itemDe" => [
                    [
                        "title" => "Collaboration",
                        "description" => "We believe in the power of working together. By fostering strong partnerships with businesses, educational institutions, and government agencies, we create opportunities that are beneficial for everyone involved.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Cultural Exchange",
                "status" => 1,
                "order" => 4, // Updated order
                "type" => 'Values',
                "setting_type" => "About",
                "itemDe" => [
                    [
                        "title" => "Cultural Exchange",
                        "description" => "We are committed to promoting cultural understanding and appreciation. Our internships are designed to immerse interns in the local culture, providing them with a deeper understanding of Cambodia and its people.",
                        "status" => 1,
                    ]
                ]
            ],
            [
                "title" => "Impact",
                "status" => 1,
                "order" => 5, // Updated order
                "type" => 'Values',
                "setting_type" => "About",
                "itemDe" => [
                    [
                        "title" => "Impact",
                        "description" => "We focus on creating positive, long-lasting impacts through our internship programs. By enhancing human capital and strengthening international ties, we contribute to the sustainable development of Cambodia.",
                        "status" => 1,
                    ]
                ]
            ],

            // endabout
            
        ];

        // $data = [
        //     [
        //         "title" => "Our Mission",
        //         "status" => 1,
        //         "order" => 1,
        //         "type" => 'AboutMission',
        //         "setting_type" => "About",
        //         "itemDe" => [
        //             [
        //                 "title" => "Our Mission",
        //                 "description" => "At Internship SEA (ISEA), our mission is to bridge the gap between the increasing demand for international internships among UK citizens and the growing need for skilled professionals in South East Asia. We aim to provide exceptional internship experiences that are mutually beneficial for interns and host organizations, contributing to the growth and development of Cambodia.",
        //                 "status" => 1,
        //             ]
        //         ]
        //     ],
        //     [
        //         "title" => "Our Vision",
        //         "status" => 1,
        //         "order" => 2, // Updated order
        //         "type" => 'AboutMission',
        //         "setting_type" => "About",
        //         "itemDe" => [
        //             [
        //                 "title" => "Our Vision",
        //                 "description" => "We envision a world where young professionals from the UK can gain invaluable international experience while contributing positively to the host country's development. By creating a platform that supports both interns and providers, we strive to be the leading facilitator of impactful internships in South East Asia, setting a benchmark for quality and service in the international internship sector.",
        //                 "status" => 1,
        //             ]
        //         ]
        //     ],
        //     [
        //         "title" => "Tom Starkey",
        //         "status" => 1,
        //         "order" => 1, // Reset order for new type
        //         "type" => 'Founders',
        //         "setting_type" => "About",
        //         "itemDe" => [
        //             [
        //                 "title" => "Tom Starkey",
        //                 "description" => "With over 15 years of international experience, Tom Starkey has a proven track record in managing development and private sector projects with interns and volunteers. He has co-founded the HR platform Next Step and managed eco-tourism projects. As the Managing Director of ISEA, Tom leads partnerships with the British Embassy and British Chamber of Commerce, oversees marketing strategies, and facilitates the entire internship process. His deep connection with Cambodia is reflected in his Cambodia Lifestyle platform, which promotes the best of Cambodian experiences.",
        //                 "status" => 1,
        //             ]
        //         ]
        //     ],
        //     [
        //         "title" => "Veronica Pou",
        //         "status" => 1,
        //         "order" => 2, // Updated order
        //         "type" => 'Founders',
        //         "setting_type" => "About",
        //         "itemDe" => [
        //             [
        //                 "title" => "Veronica Pou",
        //                 "description" => "As an HR specialist for a leading Cambodian firm, Veronica Pou brings a deep understanding of the Cambodian market and extensive connections within the recruitment landscape. She serves as the Chief Financial Operator, responsible for initial capital investment and building the financial strategy for ISEA in its first three years.",
        //                 "status" => 1,
        //             ]
        //         ]
        //     ],
        //     [
        //         "title" => "Excellence",
        //         "status" => 1,
        //         "order" => 1, // Reset order for new type
        //         "type" => 'Values',
        //         "setting_type" => "About",
        //         "itemDe" => [
        //             [
        //                 "title" => "Excellence",
        //                 "description" => "We strive to provide the highest quality of service to our interns and partner organizations, ensuring that every internship experience is enriching and valuable.",
        //                 "status" => 1,
        //             ]
        //         ]
        //     ],
        //     [
        //         "title" => "Integrity",
        //         "status" => 1,
        //         "order" => 2, // Updated order
        //         "type" => 'Values',
        //         "setting_type" => "About",
        //         "itemDe" => [
        //             [
        //                 "title" => "Integrity",
        //                 "description" => "We conduct our operations with transparency and honesty, building trust with our interns, partners, and the communities we serve.",
        //                 "status" => 1,
        //             ]
        //         ]
        //     ],
        //     [
        //         "title" => "Collaboration",
        //         "status" => 1,
        //         "order" => 3, // Updated order
        //         "type" => 'Values',
        //         "setting_type" => "About",
        //         "itemDe" => [
        //             [
        //                 "title" => "Collaboration",
        //                 "description" => "We believe in the power of working together. By fostering strong partnerships with businesses, educational institutions, and government agencies, we create opportunities that are beneficial for everyone involved.",
        //                 "status" => 1,
        //             ]
        //         ]
        //     ],
        //     [
        //         "title" => "Cultural Exchange",
        //         "status" => 1,
        //         "order" => 4, // Updated order
        //         "type" => 'Values',
        //         "setting_type" => "About",
        //         "itemDe" => [
        //             [
        //                 "title" => "Cultural Exchange",
        //                 "description" => "We are committed to promoting cultural understanding and appreciation. Our internships are designed to immerse interns in the local culture, providing them with a deeper understanding of Cambodia and its people.",
        //                 "status" => 1,
        //             ]
        //         ]
        //     ],
        //     [
        //         "title" => "Impact",
        //         "status" => 1,
        //         "order" => 5, // Updated order
        //         "type" => 'Values',
        //         "setting_type" => "About",
        //         "itemDe" => [
        //             [
        //                 "title" => "Impact",
        //                 "description" => "We focus on creating positive, long-lasting impacts through our internship programs. By enhancing human capital and strengthening international ties, we contribute to the sustainable development of Cambodia.",
        //                 "status" => 1,
        //             ]
        //         ]
        //     ],
        // ];
        

        // Insert services and details into the database
        foreach ($services as $service) {
            // Insert into our_services
            $serviceId = DB::table('isea_setting')->insertGetId([
                'title' => $service['title'],
                'status' => $service['status'],
                'order' => $service['order'],
                'type' => $service['type'],
                'setting_type' => $service['setting_type'],
            ]);

            // Insert into our_services_de
            foreach ($service['itemDe'] as $itemDe) {
                DB::table('isea_setting_de')->insert([
                    'our_service_id' => $serviceId,
                    'title' => $itemDe['title'],
                    'description' => $itemDe['description'],
                    'status' => $itemDe['status'],
                ]);
            }
        }
    }
}
