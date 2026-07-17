<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\ArticleLike;
use App\Entity\ArticleVue;
use App\Entity\Contact;
use App\Entity\Equipement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // --- 1. USERS ---
        $users = [];

        // Admin User
        $admin = new User();
        $admin->setEmail('admin@diamond.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'adminpass'));
        $manager->persist($admin);
        $users[] = $admin;

        // Test User 1 (matching typical student workspace name/contexts)
        $user1 = new User();
        $user1->setEmail('aymerick@diamond.com');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'password'));
        $manager->persist($user1);
        $users[] = $user1;

        // Other mock users
        $emails = [
            'johndoe@diamond.com',
            'baseballfan@gmail.com',
            'mlb_analyst@yahoo.com',
            'pitcher_pro@gmail.com',
            'home_run_queen@outlook.com'
        ];

        foreach ($emails as $email) {
            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $users[] = $user;
        }

        // --- 2. EQUIPEMENTS ---
        $equipementData = [
            [
                'nom' => 'Batte Rawlings Maple Ace',
                'categorie' => 'Batte',
                'description' => 'Batte de baseball professionnelle Rawlings fabriquée en bois d\'érable sélectionné de première qualité. Conçue pour offrir un excellent équilibre, une durabilité maximale et un "pop" exceptionnel à chaque impact.',
                'image' => 'https://images.unsplash.com/photo-1530541930197-ff16ac917b0e?auto=format&fit=crop&w=800&q=80',
                'sport' => 1
            ],
            [
                'nom' => 'Gant Wilson A2000 11.5"',
                'categorie' => 'Gant',
                'description' => 'Le gant Wilson A2000 est la référence absolue pour les joueurs de champ intérieur. Confectionné en cuir Pro Stock américain réputé pour sa robustesse incomparable et son confort sur-mesure.',
                'image' => 'https://images.unsplash.com/photo-1544045560-7297ff6a020b?auto=format&fit=crop&w=800&q=80',
                'sport' => 1
            ],
            [
                'nom' => 'Balles de Baseball Officielles MLB (Boîte de 12)',
                'categorie' => 'Balle',
                'description' => 'Balles officielles cousues main de la Major League Baseball. Fabriquées avec un cœur en liège et caoutchouc doublement enveloppé de laine vierge, et revêtues d\'un cuir pleine fleur de première qualité.',
                'image' => 'https://images.unsplash.com/photo-1471295263379-6cfd6d3dd024?auto=format&fit=crop&w=800&q=80',
                'sport' => 1
            ],
            [
                'nom' => 'Casque de protection Easton Pro X',
                'categorie' => 'Protection',
                'description' => 'Casque de frappeur haut de gamme doté de la technologie Multi-Density Protection (MDP) pour amortir efficacement tous types d\'impacts. Ventilation optimale et confort accru pour la boîte de frappeur.',
                'image' => 'https://images.unsplash.com/photo-1508704047492-e560116ddcb6?auto=format&fit=crop&w=800&q=80',
                'sport' => 1
            ],
            [
                'nom' => 'Crampons Under Armour Harper 8 Low',
                'categorie' => 'Chaussures',
                'description' => 'Chaussures signature Bryce Harper dotées d\'une plaque hybride combinant crampons en métal et crampons moulés en TPU. Amorti Charged Cushioning pour un retour d\'énergie maximal lors des sprints.',
                'image' => null,
                'sport' => 1
            ],
            [
                'nom' => 'Sac à dos d\'équipement DeMarini Voodoo',
                'categorie' => 'Sac',
                'description' => 'Sac à dos ultra-pratique avec un compartiment principal spacieux pour le casque et les gants, des fourreaux latéraux renforcés pouvant contenir jusqu\'à deux battes, et une poche étanche pour les chaussures.',
                'image' => null,
                'sport' => 1
            ]
        ];

        foreach ($equipementData as $data) {
            $equipement = new Equipement();
            $equipement->setNom($data['nom']);
            $equipement->setCategorie($data['categorie']);
            $equipement->setDescription($data['description']);
            $equipement->setImage($data['image']);
            $equipement->setSport($data['sport']);
            $manager->persist($equipement);
        }

        // --- 3. ARTICLES ---
        $articlesData = [
            [
                'sujet' => 'Le retour en force des New York Yankees en cette mi-saison',
                'contenu' => "Après un début de championnat mitigé, les New York Yankees semblent enfin avoir trouvé leur rythme de croisière. Portés par un Aaron Judge en feu et une rotation de lanceurs menée de main de maître par Gerrit Cole, la franchise du Bronx enchaîne les victoires.\n\nLa stratégie offensive axée sur les coups de circuit commence à porter ses fruits, et la cohésion d'équipe retrouvée dans les vestiaires laisse présager une fin de saison palpitante pour les supporters. Les playoffs sont désormais en ligne de mire et le Yankee Stadium gronde d'impatience !",
                'image' => 'https://images.unsplash.com/photo-1508704047492-e560116ddcb6?auto=format&fit=crop&w=800&q=80',
                'tags' => 'Yankees, MLB, Saison, Baseball'
            ],
            [
                'sujet' => 'Shohei Ohtani réécrit l\'histoire : vers une nouvelle performance record ?',
                'contenu' => "Le prodige japonais Shohei Ohtani continue de repousser les limites du possible dans la Major League Baseball. Après sa saison légendaire l'année passée, le joueur polyvalent des Dodgers de Los Angeles affiche des statistiques à couper le souffle tant au bâton que sur le monticule.\n\nLes analystes sportifs du monde entier se posent la même question : Ohtani va-t-il réaliser l'impensable et battre son propre record de coups de circuit tout en conservant une moyenne d'efficacité de lanceur digne des plus grands ? Une chose est sûre, nous assistons à l'histoire en marche.",
                'image' => 'https://images.unsplash.com/photo-1471295263379-6cfd6d3dd024?auto=format&fit=crop&w=800&q=80',
                'tags' => 'Ohtani, Dodgers, Record, MLB'
            ],
            [
                'sujet' => 'L\'essor du baseball en Europe : passionnés et nouveaux parcs',
                'contenu' => "Longtemps considéré comme un sport exclusivement américain ou asiatique, le baseball connaît un véritable essor sur le continent européen. En France, en Allemagne et aux Pays-Bas, de nouveaux clubs voient le jour chaque mois, attirant un public de plus en plus jeune.\n\nLa construction d'infrastructures dédiées et la diffusion des matchs majeurs via les plateformes de streaming participent grandement à cette démocratisation. Les compétitions européennes gagnent également en intensité et en niveau technique, montrant que l'Europe a toute sa place sur l'échiquier mondial.",
                'image' => 'https://images.unsplash.com/photo-1530541930197-ff16ac917b0e?auto=format&fit=crop&w=800&q=80',
                'tags' => 'Europe, Club, Sport, Croissance'
            ],
            [
                'sujet' => 'Pourquoi la WHIP est la statistique ultime pour évaluer un lanceur',
                'contenu' => "Dans le monde de la sabermétrie (l'analyse statistique du baseball), certaines données valent de l'or. C'est le cas de la WHIP (Walks plus Hits per Inning Pitched), qui mesure le nombre moyen de coureurs qu'un lanceur laisse atteindre les bases par manche.\n\nContrairement à la simple moyenne de points mérités (ERA), la WHIP isole la performance pure du lanceur de la qualité de la défense derrière lui. Cet article explore en profondeur comment calculer cette statistique essentielle et comment elle révolutionne la façon dont les managers recrutent leurs futurs lanceurs partants.",
                'image' => 'https://images.unsplash.com/photo-1544045560-7297ff6a020b?auto=format&fit=crop&w=800&q=80',
                'tags' => 'Stats, Sabermetrics, Lancers, Tactique'
            ],
            [
                'sujet' => 'Guide de démarrage : Les règles fondamentales pour apprécier un match',
                'contenu' => "Vous souhaitez vous initier au baseball mais les règles vous paraissent complexes ? Pas de panique ! Ce guide simplifié vous explique les bases essentielles : les 9 manches de jeu, la dynamique duel batteur/lanceur, les notions de strikes, balls et outs, ainsi que la manière de marquer des points (runs).\n\nEn comprenant ces principes simples, vous serez armé pour suivre et vibrer devant votre premier match complet de MLB sans vous perdre dans le jargon !",
                'image' => null,
                'tags' => 'Guide, Règles, Débutant'
            ]
        ];

        $articles = [];
        foreach ($articlesData as $idx => $data) {
            $article = new Article();
            $article->setSujet($data['sujet']);
            $article->setContenu($data['contenu']);
            $article->setImage($data['image']);
            $article->setTags($data['tags']);

            // Assign a random user as the author
            $randomAuthor = $users[array_rand($users)];
            $article->setAuthor($randomAuthor);

            // Set auteur text field
            $username = explode('@', $randomAuthor->getEmail())[0];
            $article->setAuteur(ucfirst($username));

            $manager->persist($article);
            $articles[] = $article;
        }

        // --- 4. LIKES & VIEWS ---
        foreach ($articles as $article) {
            // Add a random number of views (2 to 6 views per article)
            $viewers = $users;
            shuffle($viewers);
            $numViews = rand(2, 6);
            for ($i = 0; $i < $numViews; $i++) {
                $viewer = $viewers[$i];
                $view = new ArticleVue();
                $view->setArticle($article);
                $view->setUser($viewer);
                $manager->persist($view);
            }

            // Add a random number of likes (1 to 4 likes per article)
            $likers = $users;
            shuffle($likers);
            $numLikes = rand(1, 4);
            for ($i = 0; $i < $numLikes; $i++) {
                $liker = $likers[$i];
                $like = new ArticleLike();
                $like->setArticle($article);
                $like->setUser($liker);
                $like->setCreatedAt($faker->dateTimeBetween('-1 month', 'now'));
                $manager->persist($like);
            }
        }

        // --- 5. CONTACTS ---
        $contactSubjects = [
            'Problème de connexion',
            'Proposition de partenariat',
            'Recrutement de rédacteurs',
            'Question sur les statistiques',
            'Suggestion d\'amélioration'
        ];

        for ($i = 0; $i < 5; $i++) {
            $contact = new Contact();
            $contact->setNom($faker->name());
            $contact->setEmail($faker->safeEmail());
            $contact->setSujet($contactSubjects[$i]);
            $contact->setMessage($faker->paragraph(3));
            $manager->persist($contact);
        }

        $manager->flush();
    }
}
