<?php
// Get NFT ID from URL parameter, default to 12
$nft_id = isset($_GET['id']) ? (int)$_GET['id'] : 12;

// NFT data array - you can expand this for more NFTs
$nfts = [
    1 => [
        'title' => 'NFT #1',
        'subtitle' => '13x17 painting #1 "P!nk"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'Inspired by album cover of: P!ink. "We must never allow nudity to be prohibited—nudity is the default of normality. Nudity is Divinity." – M. J. Leonard',
        'additional_text' => [
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-1.jpg',
        'character_image' => '../assets/images/nft-character-1.jpg',
        'portrait_image' => '../assets/images/nft-portrait-1.jpg'
    ],
    2 => [
        'title' => 'NFT #2',
        'subtitle' => '13x17 painting #2 "Red and the Robot"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => '"We must never allow nudity to be prohibited. Nudity is Divinity. Nudity is normal" – M. J. Leonard',
        'additional_text' => [
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-2.jpg',
        'character_image' => '../assets/images/nft-character-2.jpg',
        'portrait_image' => '../assets/images/nft-portrait-2.jpg'
    ],
    3 => [
        'title' => 'NFT #3',
        'subtitle' => '13x17 painting #3 "Lolita Hathawatts"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'We have four separate flirts here. 1) She is winking at the viewer. 2) She twirls her hair. 3) The hand over her heart, to suggest hers beats fast. And 4) the peek-a-boo with her legs flashing her private. She is in the cockpit of a mecha in the middle of war. She is in the driver\'s seat defending nudity and love and romance.',
        'additional_text' => [
            '"We must never allow nudity to be prohibited—nudity puts us in the driver\'s seat to defend love, romance, and its manifestation—which is God." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-3.jpg',
        'character_image' => '../assets/images/nft-character-3.jpg',
        'portrait_image' => '../assets/images/nft-portrait-3.jpg'
    ],
    4 => [
        'title' => 'NFT #4',
        'subtitle' => '13x17 painting #4 "Fraya Hathawatts"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'While I am playing with rectangles, you will see (it\'s hard to see) a large circle of light that encompasses most of the girl. I envision a future where clothing is really worn.',
        'additional_text' => [
            '"We must never allow nudity to be prohibited—nudity is normal by default. Nudity is to be one with God." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-4.jpg',
        'character_image' => '../assets/images/nft-character-4.jpg',
        'portrait_image' => '../assets/images/nft-portrait-4.jpg'
    ],
    5 => [
        'title' => 'NFT #5',
        'subtitle' => '13x17 painting #5 "Lilith & Eve"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'We have two girls at play. Lilith is dancing, laughing, and in love with life. Eve is sitting out watching her sister from the corner of her eye. Eve wants to play like Lilith. I think all girls do. Both happen to be nude and in the spotlight. Eve is just nude; conversely, Lilith\'s nudity plays a role as part of her freedom. Do you have enough freedom to be nude? I am asking you the viewer: can you be nude if you really, really wanted to be?',
        'additional_text' => [
            '"We must never allow nudity to be prohibited—nudity is normal by default. Nudity is the Divine state that establishes normality." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-5.jpg',
        'character_image' => '../assets/images/nft-character-5.jpg',
        'portrait_image' => '../assets/images/nft-portrait-5.jpg'
    ],
    6 => [
        'title' => 'NFT #6',
        'subtitle' => '13x17 painting #6 "Yumaria"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'I was compelled by the government under duress and mortal fear I would be savagely beaten by police while I lay prone and helpless in no way fighting back. My broken bones, the price of freedom. I was terrified this would happen... Again! That\'s why I painted a set of clothing for her. If you wish to view this artwork correctly, place the clothing on a reproduction, and view the nude by its side. Then go look at the medical X-rays of my 3 broken rib bones.',
        'additional_text' => [
            '"We must never allow nudity to be prohibited. We must hold police, prosecutors, and judges accountable for the crimes they commit as acts of treason which destroy our country in total. A single such act of fabricating evidence by the prosecution is worse than a thousand murders—for we will face war to regain the freedoms they destroy at the cost of millions of lives." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-6.jpg',
        'character_image' => '../assets/images/nft-character-6.jpg',
        'portrait_image' => '../assets/images/nft-portrait-6.jpg'
    ],
    7 => [
        'title' => 'NFT #7',
        'subtitle' => '13x17 painting #7 "Yumaria and the Robot"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'She is so naked, the bright yellow hair forces the viewer to gaze on her flesh. The giant robot and her gun contradict the idea she can be nude. Her raised fist, paralleled by his fist, indicate the willingness to fight. Not only does the girl have the Constitutional Right to Free Speech, she also has the Constitutional Right to Bear Arms to protect her free speech. Next I want you to ponder: Did you notice the robot is nude?',
        'additional_text' => [
            '"We must never allow nudity to be prohibited. Nudity is a critical element within freedom in many ways. Nudity allows free thought. Nudity allows love → thus God. Freedom of Religion. Nudity causes privacy. Clothing identifies rank, wealth, religion, affiliation, and without clothing, those things are unknown and private." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-7.jpg',
        'character_image' => '../assets/images/nft-character-7.jpg',
        'portrait_image' => '../assets/images/nft-portrait-7.jpg'
    ],
    8 => [
        'title' => 'NFT #8',
        'subtitle' => '13x17 painting #8 "Clockwork Doll Fraya"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'Notice, she is a robot. We don\'t expect her to be nude. All machines are nude, right? But the addition of nipples and a clitoris, which are standard parts of all females, vividly express the Freedom of Speech and expose how that freedom is being dismantled.',
        'additional_text' => [
            '"We must never allow nudity to be prohibited. Nudity is the default of normality. Contemplate how ridiculous it would be to be required to \'Cloth\' your computer and car. Nudity is Divinity." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-8.jpg',
        'character_image' => '../assets/images/nft-character-8.jpg',
        'portrait_image' => '../assets/images/nft-portrait-8.jpg'
    ],
    9 => [
        'title' => 'NFT #9',
        'subtitle' => '13x17 painting #9 "Blonde Fairies"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'These girls were inspired by the movie \'Wizards\' from the \'70s. Thus the movie predated the tyrannical oppression that silences speech today.',
        'additional_text' => [
            '"We must never allow nudity to be prohibited." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-9.jpg',
        'character_image' => '../assets/images/nft-character-9.jpg',
        'portrait_image' => '../assets/images/nft-portrait-9.jpg'
    ],
    10 => [
        'title' => 'NFT #10',
        'subtitle' => '13x17 painting #10 "Orange Fairies"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'Of particular interest is the four girls of this image were the artworks depicted in the movie \'Wizards,\' which predate most of the demonization of nudity and silencing of Freedom of Speech. In the movie from the \'70s, they create a wondrous innocence and embody love. Now I use them to claim love is good and the criminalization of love is evil and demonic. Only Satan wishes to remove God; only Satan wishes to remove love. Only Satan wishes to remove nudity, which manifests love and God with it.',
        'additional_text' => [
            '"We must never allow nudity to be prohibited. That movie would absolutely be blocked, shut down, and killed were it to be made in this year 2023. This truth is foul and repugnant to the Constitution." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-10.jpg',
        'character_image' => '../assets/images/nft-character-10.jpg',
        'portrait_image' => '../assets/images/nft-portrait-10.jpg'
    ],
    11 => [
        'title' => 'NFT #11',
        'subtitle' => '13x17 painting #11 "Red Fairies"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'These girls were inspired by the nude girls in the movie \'Wizards.\' The movie predate the majority of the demonization of nudity. Thus, in the movie, the nudity is simply a wondrous love that communicates the story. As I use them here, they are a symbol of what the demonic government has robbed from the people of this country. It is a symbol of the crimes of the government against the people. It is righteous justification for revolution whereby the People are restored and tyrants slaughtered.',
        'additional_text' => [
            '"We must never allow what this image proves has already occurred. We must reclaim the Right to Free Speech that so many died to get." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-11.jpg',
        'character_image' => '../assets/images/nft-character-11.jpg',
        'portrait_image' => '../assets/images/nft-portrait-11.jpg'
    ],
    12 => [
        'title' => 'NFT #12',
        'subtitle' => '13x17 painting #12 "Elk Horn Succubus"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'I also painted a bone clothing layer on a clear plastic sheet. While I very much like the image with the censorship of her nipples and vagina, I absolutely feel the cold steel of a knife blade at my throat—it does not wish me to think. It does not want to allow any debate over the ideas. These ideas are squashed and killed by the bone clothing. This is two images—the original is nude, the plastic adds clothing. I empower the freedom to speak about both, to think about both.',
        'additional_text' => [
            '"We must never allow nudity to be prohibited—nudity is the default of normality" – M. J. Leonard',
            'To display correctly reproduce the original and display side by side with the plastic over the reproduction.',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-12.jpg',
        'character_image' => '../assets/images/nft-character-12.jpg',
        'portrait_image' => '../assets/images/nft-portrait-12.jpg'
    ],
    13 => [
        'title' => 'NFT #13',
        'subtitle' => '13x17 painting #13 "Pink Medusa"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'I painted a pink blouse and skirt on a separate transparency. To be displayed correctly, use a reproduction of the original nude to place the transparency over and view the two artworks side by side. Observe the conduct cannot have changed between the two images. Observe nudity has no power to compel conduct. The girl is exactly the same clothed as she is nude. Any and all prohibition of nudity is a silencing of speech and thinking. Those who wish to criminalize nudity, contraband nudity, censor nudity in any form are not trying to protect anyone—they are trying to enslave them.',
        'additional_text' => [
            '"We must never allow nudity to be prohibited. We can never allow our minds and ideas to be enslaved." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-13.jpg',
        'character_image' => '../assets/images/nft-character-13.jpg',
        'portrait_image' => '../assets/images/nft-portrait-13.jpg'
    ],
    14 => [
        'title' => 'NFT #14',
        'subtitle' => '13x17 painting #14 "Ecneconni on wall" - From sketch N28',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'Notice how her eyes and hair grab you. The golden flourish and mushrooms grab you. She is simply a girl sitting on a wall. The viewer is not encouraged to look at her privates and the viewer\'s eyes are quickly pulled away. Yet, she just happens to be nude.',
        'additional_text' => [
            '"We must never allow nudity to be prohibited—nudity is the normal condition and should be adopted by default. Nudity is Divinity—clothing is sin." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-14.jpg',
        'character_image' => '../assets/images/nft-character-14.jpg',
        'portrait_image' => '../assets/images/nft-portrait-14.jpg'
    ],
    15 => [
        'title' => 'NFT #15',
        'subtitle' => '13x17 painting #15 "Hippocampus" - From sketch (unnumbered)',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'To display correctly, make a reproduction to put the transparency with shells on and view side by side. Notice the nude feels organic and natural, while the small addition of shells feels awkward and manufactured. With the nude, we tend to focus on the horse (hippocampus), but with shells, the viewer focuses on the girl. There is a feeling something is not right. I wanted the viewer to feel like the clothing was wrong. I will confess—I cheated. To make the shells look \'wrong\' and cause the viewer to think the nude was better, I made the shells too light. The shells are in shadow, which is truly why they look wrong—they need to be darker. But I was not trying to paint it correctly—I wanted the viewer to think it was better nude.',
        'additional_text' => [
            '"We must never prohibit nudity—it is nature\'s truest form." – M. J. Leonard'
        ],
        'document_image' => '../assets/images/nft-document-15.jpg',
        'character_image' => '../assets/images/nft-character-15.jpg',
        'portrait_image' => '../assets/images/nft-portrait-15.jpg'
    ],
    16 => [
        'title' => 'NFT #16',
        'subtitle' => '13x17 painting #16 "Fairy N31"',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'Notice how her eyes and hair grab you. The viewer\'s eyes are pulled to her yellow wings and to the green vine segment backed by purple. The viewer must work to track down her privates, and the eyes get pulled away quickly. Yet, she just happens to be nude.',
        'additional_text' => [
            '"We must never allow nudity to be prohibited—Nudity is Divinity; nudity is the default of normality." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah'
        ],
        'document_image' => '../assets/images/nft-document-16.jpg',
        'character_image' => '../assets/images/nft-character-16.jpg',
        'portrait_image' => '../assets/images/nft-portrait-16.jpg'
    ],
    17 => [
        'title' => 'NFT #17',
        'subtitle' => '13x17 painting #17 "Astral Dragon" – 155',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'Of the three: giant emeralds, dragons, or a nude girl, the most common must be the nude girl. The most common must be the least shocking. Therefore, the nude girl is the least shocking. But is it? Have you constructed the world of your mind in a manner that, of the three, giant emerald and dragons are more common?',
        'additional_text' => [
            '"We must never allow nudity to be prohibited—Nudity is Love, and I want as much Love in the world as possible." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah'
        ],
        'document_image' => '../assets/images/nft-document-17.jpg',
        'character_image' => '../assets/images/nft-character-17.jpg',
        'portrait_image' => '../assets/images/nft-portrait-17.jpg'
    ],
    18 => [
        'title' => 'NFT #18',
        'subtitle' => '13x17 painting #18 "Flourish Chin Cat" – #240',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'The red hair, eyes, and tip of her tail grab you. There are many beautiful flourishes for the viewer\'s eyes. Oh, she is nude—it is not the focus of the image. It simply happens to be that way. She is a cat. Why? Nudity is like race. Nobody should care or pay any attention to what race you are. It should not matter if you are black, white, brown, or yellow. And it should be the last thing you acknowledge about someone. She has a tail, an axe, red hair, red eyes, has magic, is a girl, is a cat, is nude.',
        'additional_text' => [
            '"We must never allow nudity to be prohibited—the state of being nude is normal by default." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah'
        ],
        'document_image' => '../assets/images/nft-document-18.jpg',
        'character_image' => '../assets/images/nft-character-18.jpg',
        'portrait_image' => '../assets/images/nft-portrait-18.jpg'
    ],
    19 => [
        'title' => 'NFT #19',
        'subtitle' => '13x17 painting #19 "Dark Nymph" #N38',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'We must be careful about what we predict. This girl is a vampire. She sits on a throne under the full moon, in the dark of night. She is also a lesbian in love. Look to her chair to see the shield. She is a fairy with wings. Her nudity is not relevant to any of these things, and that\'s the point. Nudity is normal and gives us no information. Nudity tells us nothing but truth. While all clothing is a lie—even if by accident. The police officer\'s uniform claims he is honest and good. But he beats his wife, cheats on his taxes, and kicks his dog.',
        'additional_text' => [
            '"We must never allow truth or nudity to be prohibited—nudity is truth of who we are for better or worse. To prohibit nudity is to prohibit truth." – M. J. Leonard'
        ],
        'document_image' => '../assets/images/nft-document-19.jpg',
        'character_image' => '../assets/images/nft-character-19.jpg',
        'portrait_image' => '../assets/images/nft-portrait-19.jpg'
    ],
    20 => [
        'title' => 'NFT #20',
        'subtitle' => '13x17 painting #20 "Fairy Dragon" sketch #153',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'There is magic in love. Its best depiction is nude. Gigantic rubies and a red dragon show us the magic that can be had if and only if the girl is nude. An act to censor her nudity is to imprison her free will. The world becomes tarnished and simply is not as believable. It would be similar to a picture of a Jewish party in a concentration camp.',
        'additional_text' => [
            '"We must never allow truth or nudity to be prohibited. Nudity is the default of normality. Nudity is Divinity." – M. J. Leonard'
        ],
        'document_image' => '../assets/images/nft-document-20.jpg',
        'character_image' => '../assets/images/nft-character-20.jpg',
        'portrait_image' => '../assets/images/nft-portrait-20.jpg'
    ],
    21 => [
        'title' => 'NFT #21',
        'subtitle' => '13x17 painting #21 "Dryad" Sketch #70',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'I love her. Looking at her (for me) invokes feelings of love and joy. I want to care for her and shelter her. I want to protect her from all the evils of the world. I want to hold her for all the days of my life. This is the set of emotions invoked by nudity. Notice all of the emotions are consistent with a faith in a kind and loving God.',
        'additional_text' => [
            '"We must never allow truth or nudity to be prohibited.—Nudity invokes love. Love is God. The death of nudity parallels the death of God." – M. J. Leonard'
        ],
        'document_image' => '../assets/images/nft-document-21.jpg',
        'character_image' => '../assets/images/nft-character-21.jpg',
        'portrait_image' => '../assets/images/nft-portrait-21.jpg'
    ],
    22 => [
        'title' => 'NFT #22',
        'subtitle' => '13x17 painting #22 "Horn Devil" Sketch #159',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'Notice how her eyes and hair grab you. The red wings and bright green floor grab you. She is simply a girl devil with wings and a tail. The viewer must work to focus on her privates, and the viewer\'s eyes are pulled down to her foot or up to that blue hair. Yet she happens to be nude.',
        'additional_text' => [
            '"We must never allow nudity to be prohibited.—Nudity is the default of normality. Nudity is Divinity." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-22.jpg',
        'character_image' => '../assets/images/nft-character-22.jpg',
        'portrait_image' => '../assets/images/nft-portrait-22.jpg'
    ],
    23 => [
        'title' => 'NFT #23',
        'subtitle' => '13x17 painting #23 "Spirit Owl" Sketch #228',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'Her eyes grab the viewer. The viewer can work to focus on her privates, but her hair, that owl, and the purple floor pull the viewer away. She is simply a girl with an owl, and she happens to be nude.',
        'additional_text' => [
            '"We must never allow nudity to be prohibited. Nudity is the default of normality. Nudity is Divinity." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-23.jpg',
        'character_image' => '../assets/images/nft-character-23.jpg',
        'portrait_image' => '../assets/images/nft-portrait-23.jpg'
    ],
    24 => [
        'title' => 'NFT #24',
        'subtitle' => '13x17 painting #24 "Jaguar Dryad" Sketch #238',
        'author' => 'Michael J. Leonard',
        'collection' => 'NFT RY VAH',
        'status' => '©2023 RYVAH',
        'main_text' => 'I am strengthening the Constitutional Right to Freedom of Speech by normalizing nudity.',
        'description' => 'Notice how the eyes and hair grab the viewer\'s focus. The bell and blue heart grab the viewer\'s focus. The pattern on the cat body does it next. She is a cat girl who happens to be nude. The viewer must work to focus on her privates. If the viewer does, then the action of the viewer is commentary about the viewer—not the artwork.',
        'additional_text' => [
            '"We must never allow nudity to be prohibited. Nudity is the default state of normality. Nudity is Divinity because it manifests love, and love is God." – M. J. Leonard',
            'Please read and pass the Laws of Ryvah.'
        ],
        'document_image' => '../assets/images/nft-document-24.jpg',
        'character_image' => '../assets/images/nft-character-24.jpg',
        'portrait_image' => '../assets/images/nft-portrait-24.jpg'
    ]
    // Add more NFTs as needed
];

// Check if NFT exists, fallback to 12 if not
if (!isset($nfts[$nft_id])) {
    $nft_id = 12;
}

$current_nft = $nfts[$nft_id];

include '../includes/layouts/header.php';
?>

<!-- Link to custom NFT styles -->
<link rel="stylesheet" href="nft-styles.css">

<!-- NFT Detail Section -->
<section class="nft-showcase">
    <div class="container-fluid">
        <!-- Top Section: Split layout for header content -->
        <div class="row no-gutters">
            <!-- Left Panel - NFT Header & Document Image -->
            <div class="col-md-6 nft-details-panel">
                <div class="nft-document">
                    <!-- Back to Collection Link -->
                    <div class="back-link mb-3">
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back to Collection
                        </a>
                    </div>

                    <!-- Document Header -->
                    <div class="document-header">
                        <h1 class="nft-title"><?php echo htmlspecialchars($current_nft['title']); ?></h1>
                        <div class="coming-soon-badge">
                            <span><?php echo htmlspecialchars($current_nft['status']); ?></span>
                        </div>

                        <!-- Document Image - Below Coming Soon -->
                        <div class="document-image-container mt-3">
                            <?php if (file_exists($current_nft['document_image'])): ?>
                            <img src="<?php echo $current_nft['document_image']; ?>"
                                alt="<?php echo $current_nft['title']; ?> Document" class="document-image">
                            <?php else: ?>
                            <div class="document-image-placeholder">
                                <div class="placeholder-content">
                                    <i class="fas fa-file-image fa-2x mb-2"></i>
                                    <p class="mb-0"><small>Document Image</small></p>
                                    <p class="mb-0"><small><?php echo $current_nft['title']; ?></small></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Credits Section -->
                    <div class="document-credits">
                        <p class="document-text">
                            <?php echo htmlspecialchars($current_nft['title']); ?><br>
                            by <?php echo htmlspecialchars($current_nft['author']); ?><br>
                            <em><?php echo htmlspecialchars($current_nft['collection']); ?></em>
                        </p>

                        <p class="document-text">
                            <strong><?php echo htmlspecialchars($current_nft['subtitle']); ?></strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Panel - NFT Artwork (Fixed Height) -->
            <div class="col-md-6 nft-artwork-panel">
                <div class="artwork-container">
                    <div class="character-illustration">
                        <!-- RYVAH Branding Header -->
                        <div class="ryvah-branding">
                            <h2 class="ryvah-title">RYVAH</h2>
                        </div>

                        <!-- Main character artwork -->
                        <div class="character-image">
                            <?php if (file_exists($current_nft['character_image'])): ?>
                            <img src="<?php echo $current_nft['character_image']; ?>"
                                alt="<?php echo $current_nft['title']; ?> Character"
                                class="img-fluid character-artwork">
                            <?php else: ?>
                            <div class="character-placeholder">
                                <div class="text-center">
                                    <div class="anime-character-placeholder">
                                        <div class="character-silhouette"></div>
                                        <p class="character-label">Anime
                                            Character<br><?php echo $current_nft['title']; ?></p>
                                        <small class="upload-hint">Upload:
                                            nft-character-<?php echo $nft_id; ?>.jpg</small>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Website watermark -->
                        <div class="website-badge">
                            <span class="site-name">RyvahCommerce.com</span>
                        </div>

                        <!-- Small portrait in corner -->
                        <div class="corner-portrait">
                            <?php if (file_exists($current_nft['portrait_image'])): ?>
                            <img src="<?php echo $current_nft['portrait_image']; ?>" alt="Character Portrait"
                                class="portrait-img">
                            <?php else: ?>
                            <div class="portrait-placeholder">
                                <div class="mini-portrait"></div>
                                <small class="portrait-hint">Portrait</small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section: Full-width flowing text content -->
        <div class="row no-gutters">
            <div class="col-12">
                <div class="nft-content-flow">
                    <div class="document-content-extended">
                        <div class="document-section">
                            <p class="document-text">
                                <?php echo htmlspecialchars($current_nft['main_text']); ?>
                            </p>

                            <p class="document-text">
                                <?php echo htmlspecialchars($current_nft['description']); ?>
                            </p>

                            <?php foreach ($current_nft['additional_text'] as $text): ?>
                            <p class="document-text">
                                <?php echo htmlspecialchars($text); ?>
                            </p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Additional styles for detail page */
.back-link {
    border-bottom: 1px solid #ddd;
    padding-bottom: 15px;
}

.nft-navigation {
    border-top: 2px solid #8B4513;
    padding-top: 30px;
    margin-top: 40px;
}

.nft-navigation h4 {
    color: #8B4513;
    font-family: 'Georgia', serif;
}

.nft-navigation .btn {
    transition: all 0.3s ease;
}

.nft-navigation .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Make sure window title shows NFT title */
</style>

<script>
// Update page title to show current NFT
document.title = '<?php echo htmlspecialchars($current_nft['title']); ?> - Ryvah NFT Collection';
</script>

<?php include '../includes/layouts/footer.php'; ?>