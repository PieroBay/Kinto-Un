#Framework

Pas encore de nom.

Utilise le template Twig pour les vue


## Controller

Seulement 2 Controller

* PublicController
* AdminController

Dans le dossier views/pages/, créer les dossiers `admin` et `public` (Sans Controller à la fin) 

Dans les pages Controller (publicController et adminController) créer des action, le nom des action auront le même nom que les pages dans les vues sans 'Action' à la fin.

Le publicController n'apparait pas dans l'url, uniquement l'adminController sera affiché.

### Récuperer donnée de la DB

Dans le controller, dans le tableau `$table`, séparer par des virgules les tables nécessaire pour les actions.

un model principal avec des requetes de base est automatiquement créé.

Si on veut des requètes spéciale, créer un fichier tableModel.php (table est le nom de la table) dans le dossier `models`.
Dans ce fichier, créer une classe du même nom que le fichier (tableModel.php) qui extends de `Model`.

```php
<?php
class tableModel extends Model{

	public function enregistrementSpecial(){
		// requète
	}
}
?>
```

Pour utiliser ces requetes dans le controller, utiliser `$this->tableModel->fonction`.

Requètes principales:

* `delete($id)` => DELETE FROM $table WHERE id = $id
* `findAll(array())` => Dans le tableau, mettre des conditions => "condition", "fields", "limit", "order"
* `findById($id)` => SELECT * FROM $table WHERE id = $id
* `save($_POST)` => $_POST ou array en paramètre => si le tableau contient un id, un UPDATE sera fait sinon un INSERT
* `id` ($this->table->id) => Récupere le dernier id ajouté si un save() a été utilisé juste avant

### Envoyer donnée de la DB à la vue

Dans les actions, quand les données sont récupérées, on les envoi à la vue grâce à la function send() avec un tableau en paramètre.
`$this->send(array())`

### Redirection

`$this->redirectUrl('public:index.html.twig', $key);` 

-`public` est le nom du controller (sans Controller à la fin);
-`index.html.twig` est le nom de la vue.
-`$key` est la clé a envoyer dans l'url si on redirige vers une vue qui en a besoin (optionel)


### Message Flash

Créer un message Flash `$this->Session->setFlash('error', 'Mauvais identifiant');` error ou ok

envoyer le message à la vue:
```php
$this->render(array(
	"flash"	=>	$this->Session->flash(),
));
```

Recuperer le message dans la vue twig: `{{ flash }}` , renvois une div `<div class="flash flash-'type'">message</div>` (type = error ou ok).

### Connexion

Utiliser `$this->table->connexion($_POST);` dans une action au nom de votre choix pour un connexion.
tester `$this->user->connexion`, si tout est ok, return true

```php
$this->user->connexion($_POST); // envoi le formulaire
if($this->user->connexion){ // si la connexion s'est bien passée, return true
  $this->redirectUrl('public:choice.html.twig'); // on redirige
}else{
	$this->Session->setFlash('error', 'Mauvais identifiant'); // sinon on envois un message flash
}
```

Dans la table qui contient les utilisateurs, ajouter un champs `role` pour pouvoir gerer les droits d'accès.

Tout visiteur est automatiquement authentifier en tant que ROLE 'visiteur'.

Pour limiter l'accès à certaine pages, utiliser: 	

```php
if($_SESSION['ROLE'] != 'admin'){ // exemple, la page est limité aux membres qui ont comme ROLE admin
  $this->redirectUrl('public:index.html.twig'); // ceux qui ne sont pas admin, ils seront rediriger sur l'index
}
```

### Deconnexion

Utiliser le code ci bas dans une action au nom de votre choix pour une déconnexion.
```php
$this->user->deconnexion(); // modifier le ROLE actuel en 'visiteur'
$this->redirectUrl('public:index.html.twig'); // une fois fait, redirection sur l'index
```

## Views

Les vues sont dans le dossier `views` -> `pages`.
Les pages sont les mêmes que les functions actions dans les controllers sans 'Action' à la fin et sont placés dans le dossier des controller ou sont les actions et ont comme extension `.html.twig`

Le dossier `Ressources` contient les fichiers Css/Js/Images/Fonts, chacun sont dans un dossier respectif.
