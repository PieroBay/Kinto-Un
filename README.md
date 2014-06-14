# Kinto'un [Framework]

[Utilise le template Twig pour les vues] 

(temporaire, dans la prochaine version, l'utilisateur aura le choix entre l'**HTML standard**, le template **Twig** ou **Smarty**)

## Fichier config

Le fichier de config (config.yml) qui vous permetra de vous connecter à la DB et d'indiquer le projet de lancement se trouve dans le dossier `core`.


## Project

Les projets sont des dossiers contenant un enssemble de code permettant par la suite d'être réutlisé plus facilement.

Les projets sont à placer dans le dossier `src/project`.

Le projet principal (qui sera lancé en premier en visitant le site) doit être indiqué dans le fichier `config.yml` se trouvant dans le dossier `core`.

/!\ Il ne peut pas y avoir un projet nommé `admin` ou il y aura conflit avec le controller du même nom.


![image](http://img4.hostingpics.net/pics/896485project.png)

## Controller

Seulement 2 Controller

* PublicController
* AdminController

À placer dans le dossier `src/project/*nomDuProjet*/controller/`.

Dans le dossier **views/**, créer les dossiers `admin` et `public` **(Sans Controller à la fin)** 

![image](http://img11.hostingpics.net/pics/400637controller.png)

Dans les pages Controller (publicController et adminController) créer des action, le nom des actions auront le même nom que les pages dans les vues sans 'Action' à la fin.

![image](http://imageshack.com/a/img843/5844/7glw.png)

Le publicController (/public/) n'apparait pas dans l'url, uniquement l'adminController (/admin/) sera affiché.

### Récuperer donnée de la DB

Dans le controller, dans le tableau `$table`, séparer par des virgules les tables nécessaire pour les actions.

![image](http://imageshack.com/a/img541/8637/c9ef.png)

un model principal avec des requetes de base est automatiquement créé.

Si on veut des requètes spéciale, créer un fichier table1Model.php (prennons l'exemple de la photo du dessus)(table1 est le nom de la table) dans le dossier `models`.
Dans ce fichier, créer une classe du même nom que le fichier (table1Model.php) qui extends de `Model`.

```php
<?php
class table1Model extends Model{

	public function enregistrementSpecial(){
		// requète
	}
}
?>
```

Pour utiliser ces requetes dans le controller, utiliser `$this->table1Model->enregistrementSpecial()`.

Requètes principales:

* `$this->table1->delete($id)` => DELETE FROM $table WHERE id = $id
* `$this->table1->findAll(array())` => Dans le tableau, mettre des conditions => "condition", "fields", "limit", "order"
* `$this->table1->findById($id)` => SELECT * FROM $table WHERE id = $id
* `$this->table1->save($_POST)` => $_POST ou array en paramètre => si le tableau contient un id, un UPDATE sera fait sinon un INSERT
* `$this->table1->id` ($this->table->id) => Récupere le dernier id ajouté si un save() a été utilisé juste avant

### Envoyer donnée de la DB à la vue

Dans les actions, quand les données sont récupérées, on les envoi à la vue grâce à la function render() avec un tableau en paramètre.
`$this->render(array())`

*Exemple:*

```php
$this->render(array(
	'message' => $listes_message,
));
```


### Message Flash

Créer un message Flash `$this->Session->setFlash('error', 'Mauvais identifiant');` ('error' ou 'ok' en premier paramètre)

envoyer le message à la vue:
```php
$this->render(array(
	"flash"	=>	$this->Session->flash(),
));
```

Recuperer le message dans la vue twig: `{{ flash }}` , renvois une div `<div class="flash flash-'type'">message</div>` (type = error ou ok).

### Redirection

`$this->redirectUrl('public:index.html.twig', $key);` 

-`public` est le nom du controller (sans Controller à la fin);
-`index.html.twig` est le nom de la vue.
-`$key` est la clé a envoyer dans l'url si on redirige vers une vue qui en a besoin (optionel)

### ROLE 

Dans la table qui contient les utilisateurs, ajouter un champs `role` pour pouvoir gerer les droits d'accès.

Tout visiteur est automatiquement authentifier en tant que ROLE **'visiteur'**.

Pour limiter l'accès à certaine pages, utiliser: 	

```php
if(!$this->ROLE('admin')){ // exemple, la page est limité aux membres qui ont comme ROLE admin.
  $this->redirectUrl('public:index.html.twig'); // ceux qui ne sont pas admin, ils seront rediriger sur l'index
}
```

Par défaut, `$this->ROLE()` contient 'visiteur' et return true. 

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


### Deconnexion

Utiliser le code ci bas dans une action au nom de votre choix pour une déconnexion.
```php
$this->user->deconnexion(); // modifier le ROLE actuel en 'visiteur'
$this->redirectUrl('public:index.html.twig'); // une fois fait, redirection sur l'index
```

## Views

Les vues sont dans le dossier `views`.

Les pages ont les mêmes nom que les actions dans les controllers sans 'Action' à la fin et sont placées dans le dossier des controllers et ont comme extension `.html.twig`.


### Ressources
Les ressources Css/Js/Images/fonts, sont à placer dans `src / ressources`, chacun sont dans un dossier respectif.

![image](http://img11.hostingpics.net/pics/561450ressources.png)

Dans les vues, le lien pour accèder aux ressources sera

`href="{{ Info.Webroot }}/src/ressources/css/style.css"`

## Error

Il est possible de générer des erreurs web.

Un template est mis en place dans le dossier `core/errors/`.

Pour déclarer l'érreur

```php
$error->generate('404',"La page que vous tentez d'atteindre n'existe pas ou n'est plus disponible.");
```

*Par défaut, l'erreur est une erreur 404*