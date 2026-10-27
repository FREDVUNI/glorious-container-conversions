<?php
declare(strict_types=1);
/**
 * The thirteen projects from the approved prototype. Photos are bundled with
 * the theme until the client uploads their own; generated from the prototype's
 * project data. Edit the records in wp-admin after activation, not here.
 */
return [
    'categories' => [
        'healthcare' => 'Healthcare',
        'workspaces' => 'Offices & workspaces',
        'homes' => 'Homes',
        'cold-storage' => 'Cold storage',
        'ablution' => 'Ablution',
        'shops' => 'Shops & kiosks',
        'fabrication' => 'Fabrication & delivery',
    ],
    'projects' => [
        [
            'slug' => 'container-hospital',
            'title' => 'Shipping Container Hospital',
            'category' => 'healthcare',
            'ref' => 'GCC-24-05-H',
            'size' => 'wide',
            'excerpt' => 'A two-storey "hospitainer" block built from stacked containers: self-contained clinical rooms with their own ventilation, lighting and power connection, designed to be set up quickly and relocated when a community’s needs change.',
            'specs' => 'Type: Two-storey stacked block
Use: Medical practice, outreach clinics
Power: Grid, mobile generator, or both
Published: May 2024',
            'features' => 'Stackable and scalable in many configurations
Dedicated AC, ventilation and lighting per unit
Roof, insulation, external wall facing, floors, windows and partitions included
Walkway and balcony access to upper units
Stored and reused for future deployment',
            'confirm' => 'no',
            'featured' => '1',
            'bundled' => 'hospital-01,hospital-02,hospital-03,hospital-04,hospital-05,hospital-06',
            'alts' => 'Two-storey blue container hospital block with upper walkway and green trim
Front elevation of the stacked container hospital, Glorious Container banner on the facade
Ground-floor walkway along the container units
Corridor of doors along the container clinic
Open door into a fitted consultation room
Wide view of the two-storey container block',
        ],
        [
            'slug' => 'call-centre-studio',
            'title' => '40ft Call Centre & Recording Studio',
            'category' => 'workspaces',
            'ref' => 'GCC-24-05-C',
            'size' => 'tall',
            'excerpt' => 'A single 40ft high-cube unit delivered ready for immediate use: a manager’s office at one end, open-plan desks in the middle, a kitchenette, and a soundproofed recording and editing studio at the far end.',
            'specs' => 'Container: 1 × 40ft high-cube
Use: Call centre, sound studio
Exterior: Half timber cladding, modified paint
Flooring: Interlocking wood and SPC',
            'features' => 'Wall and ceiling insulation throughout
Windows with mosquito nets, personnel doors
Air conditioning and off-grid ventilation
Network and electrical fit-out, MDF desktops
Soundproofed studio room',
            'confirm' => 'no',
            'featured' => '3',
            'bundled' => 'hero-call-centre,callcentre-02,callcentre-06,callcentre-03,callcentre-04,callcentre-05',
            'alts' => 'White and green 40ft container office with timber panels and a pitched canopy roof
Front of the call centre unit set in a garden
Side elevation showing half timber cladding and windows
Entrance door of the call centre container
External air-conditioning unit mounted on the container wall
Close-up of the cladding and canopy detail',
        ],
        [
            'slug' => 'cold-storage',
            'title' => 'Cool Room & Refrigerated Cold Storage',
            'category' => 'cold-storage',
            'ref' => 'GCC-24-05-R',
            'size' => 'normal',
            'excerpt' => 'Refrigerated container units with thermostatic control, inspected before delivery and commissioned on site by a qualified refrigeration mechanic. Suitable for food, flowers, drinks and pharmaceutical storage.',
            'specs' => 'Type: Insulated refrigerated unit
Control: Thermostatic, optional digital display and alarms
Support: 24/7 breakdown service within ~50 km of Kampala
Published: May 2024',
            'features' => 'Pre-delivery inspection of every unit
Single-door access so the cargo doors stay sealed
Optional online management app
Installation by a qualified refrigeration mechanic',
            'confirm' => 'no',
            'featured' => '2',
            'bundled' => 'coldroom-01,coldroom-02,coldroom-03,coldroom-04,coldroom-05',
            'alts' => 'White insulated cold-room container in a yard
Cold-room unit with single access door, front view
Cold-room unit seen from the side
Technician fitting the refrigeration unit inside
Cold-room container beside a rust-red storage unit',
        ],
        [
            'slug' => 'sliding-doors',
            'title' => 'Internal Glass Sliding Doors',
            'category' => 'healthcare',
            'ref' => 'GCC-24-05-D',
            'size' => 'normal',
            'excerpt' => 'Fit-out detail from a hospitainer build: an internal glass sliding door and large windows bring daylight deep into the unit, cut electricity use and make the most of a container’s 8ft structural width.',
            'specs' => 'Type: Interior fit-out detail
Width: 8ft structural width
Use: Portable clinic
Published: May 2024',
            'features' => 'Internal glass sliding door
Large windows for natural daylight
Space-efficient layout
Portable and easily moved',
            'confirm' => 'no',
            'featured' => '',
            'bundled' => 'sliding-01,sliding-02,sliding-03',
            'alts' => 'Plywood-lined container interior with a steel-framed sliding door track
Sliding door frame during fit-out, portrait view
Detail of the sliding door runner and ply lining',
        ],
        [
            'slug' => 'home-red-40ft',
            'title' => '40ft Home with Pitched Roof',
            'category' => 'homes',
            'ref' => 'GCC-RES-01',
            'size' => 'wide',
            'excerpt' => 'Before and after: a used 40ft container set on a masonry plinth, then cut for windows and a door and finished with an iron-sheet pitched roof for shade and rain protection.',
            'specs' => 'Container: 1 × 40ft
Roof: Pitched iron-sheet roof
Base: Masonry plinth
Status: Client to confirm details',
            'features' => 'Window and door openings cut and framed
Pitched roof reduces heat gain and sheds rain
Raised plinth keeps the floor dry
Retains original corten-red finish',
            'confirm' => 'yes',
            'featured' => '',
            'bundled' => 'home-red-before,home-red-after',
            'alts' => 'Before: a rust-red 40ft container placed on a stone plinth, no openings yet
After: the same container with windows, a door and a new pitched roof',
        ],
        [
            'slug' => 'home-blue',
            'title' => 'Blue Container Residence',
            'category' => 'homes',
            'ref' => 'GCC-RES-02',
            'size' => 'normal',
            'excerpt' => 'A single-storey container home in bright blue, with white-framed windows, a planted garden and a pitched roof added over the unit during the build.',
            'specs' => 'Type: Single-storey home
Finish: Blue exterior, white joinery
Roof: Added pitched roof
Status: Client to confirm details',
            'features' => 'Multiple windows and personnel doors
Roof structure fitted on site
Garden landscaping around the unit',
            'confirm' => 'yes',
            'featured' => '',
            'bundled' => 'home-blue-01,home-blue-02,home-blue-03,home-blue-04',
            'alts' => 'Blue container home with white windows and a garden hedge
Blue container home on open ground
Blue container with open doors during finishing
Roof structure being fitted over the blue container',
        ],
        [
            'slug' => 'home-grey',
            'title' => 'Grey Home with Verandah Roof',
            'category' => 'homes',
            'ref' => 'GCC-RES-03',
            'size' => 'normal',
            'excerpt' => 'A compact grey container home with a full pitched roof extending into a small verandah, security-grilled windows and an external AC unit.',
            'specs' => 'Type: Single-unit home
Roof: Pitched roof with verandah
Finish: Grey exterior
Status: Client to confirm details',
            'features' => 'Pitched roof with verandah overhang
Security grilles on windows
Air conditioning fitted',
            'confirm' => 'yes',
            'featured' => '',
            'bundled' => 'home-grey-01',
            'alts' => 'Grey container home with a pitched roof and small verandah',
        ],
        [
            'slug' => 'home-green',
            'title' => 'Green Residence with Canopy',
            'category' => 'homes',
            'ref' => 'GCC-RES-04',
            'size' => 'normal',
            'excerpt' => 'A green container residence under a steel canopy roof, with a timber garden store and planted surroundings.',
            'specs' => 'Type: Residential unit
Roof: Free-standing steel canopy
Finish: Green exterior
Status: Client to confirm details',
            'features' => 'Steel canopy roof over the unit
Timber outbuilding
Water storage tank',
            'confirm' => 'yes',
            'featured' => '',
            'bundled' => 'home-green-01,home-green-02',
            'alts' => 'Green container residence under a steel canopy, viewed across the garden
Green container residence with timber garden store in the foreground',
        ],
        [
            'slug' => 'office-interiors',
            'title' => 'Fitted Office Interiors',
            'category' => 'workspaces',
            'ref' => 'GCC-OFF-01',
            'size' => 'normal',
            'excerpt' => 'Inside finished container offices: lined and painted walls, timber floors, cove lighting, air conditioning, and partitioned workstations ready for desks and equipment.',
            'specs' => 'Type: Office fit-out
Lining: Ply or melamine, insulated
Services: Electrical, AC, lighting',
            'features' => 'Insulated and lined interiors
Timber-effect flooring
Partitioned workstations
Cove lighting and AC',
            'confirm' => 'no',
            'featured' => '',
            'bundled' => 'office-int-01,office-int-02,office-int-03,office-int-04',
            'alts' => 'Finished office interior with desks, AC and cove lighting
Office interior looking towards the open door
Boardroom table and chairs inside a container office
Yellow-painted interior with partitioned workstations',
        ],
        [
            'slug' => 'ablution-unit',
            'title' => 'Ablution Unit',
            'category' => 'ablution',
            'ref' => 'GCC-ABL-01',
            'size' => 'normal',
            'excerpt' => 'Portable toilet and shower cabins built from containers, from single WCs to fully fitted welfare blocks with water heater, hand basin and timber-framed doors.',
            'specs' => 'Type: Ablution block
Fittings: WC, basin, shower, water heater
Use: Sites, events, visitor attractions',
            'features' => 'Water-supply connection and heater
Shower cubicle option
Disabled-access option
Hygienic wipe-down finishes',
            'confirm' => 'no',
            'featured' => '',
            'bundled' => 'ablution-03,ablution-01,ablution-02',
            'alts' => 'Two toilet cubicles with timber-framed doors inside a container
Toilet cubicle with wall-mounted water heater
Corner hand basin inside the ablution unit',
        ],
        [
            'slug' => 'fabrication',
            'title' => 'Stacking, Insulation & Delivery',
            'category' => 'fabrication',
            'ref' => 'GCC-YARD',
            'size' => 'tall',
            'excerpt' => 'Behind the scenes at the Bugolobi yard and on site: units craned into position, 40ft boxes stacked two high, insulation fitted between studs, and exteriors prepared for branding.',
            'specs' => 'Where: Bugolobi Industrial Area, Kampala
Transport: In-house crane and haulage
Insulation: Fibre batts between timber studs',
            'features' => 'Crane placement on prepared bases
Two-high stacking with corner locks
Insulation and first-fix electrics
Exterior branding and paint',
            'confirm' => 'no',
            'featured' => '',
            'bundled' => 'stack-01,crane-01,insulation-01,branding-01,storage-01',
            'alts' => 'Two containers stacked on top of a yellow container, crane cable visible
Mobile crane lifting a container off a truck on site
Insulation batts fitted between timber studs inside a container
Worker applying vinyl branding to a blue container wall
Row of beige storage containers with open doors',
        ],
        [
            'slug' => 'timber-kiosk',
            'title' => 'Timber-Clad Kiosk',
            'category' => 'shops',
            'ref' => 'GCC-SHP-01',
            'size' => 'normal',
            'excerpt' => 'A 20ft unit wrapped in horizontal timber cladding with a lift-up serving hatch and counter, suited to coffee, food and drink service at events and roadside pitches.',
            'specs' => 'Container: 20ft
Exterior: Timber cladding
Opening: Lift-up hatch with counter
Status: Client to confirm details',
            'features' => 'Full-width serving hatch
External counter
Lattice screen for shade
Sliding window on the rear elevation',
            'confirm' => 'yes',
            'featured' => '',
            'bundled' => 'kiosk-01,kiosk-02,kiosk-03',
            'alts' => 'Timber-clad container kiosk with lift-up hatch and serving counter
Kiosk with a timber lattice screen to one side
Rear of the timber-clad kiosk with sliding window',
        ],
        [
            'slug' => 'red-20ft',
            'title' => 'Compact 20ft Unit',
            'category' => 'shops',
            'ref' => 'GCC-SHP-02',
            'size' => 'normal',
            'excerpt' => 'A 20ft container in high-gloss red with a cut window and a side door, delivered as a compact shop or site office.',
            'specs' => 'Container: 20ft
Finish: Gloss red
Openings: Window and side door
Status: Client to confirm details',
            'features' => 'Gloss exterior paint
Framed window and door
Set on blocks for drainage',
            'confirm' => 'yes',
            'featured' => '',
            'bundled' => 'unit-red-01',
            'alts' => 'Gloss red 20ft container with a window and side door',
        ],
    ],
];
