# Kinto'un [Framework]
v 0.8.5

Nécessite PHP 5.4 ou +

[Utilise le template Twig/Smarty ou Php classique] 


## Installation

Extraire le dossier `Framework` à la racine de votre site. (/!\ N'oubliez pas le `.htaccess` )
Configurez le dossier `config.yml` se trouvant à `core/config.yml`, spécifiez correctement les champs demandés.


## Fichier config

Le fichier de config `config.yml` se trouvant à `core/config.yml` vous permettra de vous connecter à la DB, de choisir le template (none/php, Twig ou Smarty) et d'indiquer le projet principal.


## Project

Les projets sont des dossiers contenant un enssemble de code permettant par la suite d'être réutlisé plus facilement.

Les projets sont à placer dans le dossier `src/project`.

Le projet principal (qui sera lancé en premier en visitant le site) doit être indiqué dans le fichier `config.yml` se trouvant dans le dossier `core`.

/!\ Il ne peut pas y avoir un projet nommé `admin` ou il y aura conflit avec le controller du même nom.


![image](http://img15.hostingpics.net/pics/477383project.png)

## Controller

Seulement 2 Controllers

* PublicController
* AdminController

À placer dans le dossier `src/project/*nomDuProjet*/controller/`.

![image](http://img15.hostingpics.net/pics/677139controller.png)

Dans le dossier **views/**, créez les dossiers `admin` et `public` **(Sans 'Controller' à la fin)** 

![image](http://img15.hostingpics.net/pics/237846views.png)

Dans les pages Controller (publicController et adminController) créez des actions, le nom des actions auront le même nom que les pages dans les vues **(sans 'Action' à la fin)**.

![image](http://img15.hostingpics.net/pics/932424action.png)


Si il y a un paramètre dans le controller, ca récupérera le paramètre dans l'url juste après le nom de l'action.


/!\ Le publicController (/public/) n'apparait pas dans l'url, uniquement le controller Admin (/admin/) peut être affiché.

### Récuperer des données de la DB

Dans le controller, dans le tableau `$table`, séparez par des virgules les tables nécessaires pour les actions.

![image](http://imageshack.com/a/img541/8637/c9ef.png)

Un model principal avec des requetes de base est par défaut dans le framework.

Si vous désirez des requètes personelles, créez un fichier table1Model.php (prennons l'exemple de la photo du dessus, table1 est le nom de la table) dans le dossier `models`.
Dans ce fichier, créer une classe du même nom que le fichier (table1Model.php) qui extends de `Model`.

```php
<?php
class table1Model extends Model{

	public function enregistrementPersonnel(){
		// requète(s)
	}
}
?>
```

Pour utiliser ces requetes dans le controller, utilisez `$this->table1Model->enregistrementPersonnel()`.

Requètes principales:

* `$this->table1->delete($id)` => DELETE FROM $table WHERE id = $id

* `$this->table1->findAll(array())` => Dans le tableau, mettre des conditions => "condition", "fields", "limit", "order"

* `$this->table1->findById($id)` => SELECT * FROM $table WHERE id = $id

* `$this->table1->save($_POST, $upload)` => $_POST ou array en paramètre => si le tableau contient un id, un UPDATE sera fait sinon un INSERT. 
$upload (non obligatoire si pas d'upload dans le formulaire) est un array avec plusieurs parametres.

```php
array(
			"target"    =>	"folder_name", # le dossier sera dans "src/ressources/images/"
			"maxSize"   => 2097152, # poids max en byte
			"widthMax"  => 1000, # largeur max en pixel
			"heightMax" => 1000, # hauteur max en pixel
			"ext"       => array('jpg','png','jpeg'), # extensions autorisées
			"red"       => false,) # Si l'image doit être redimensionner mettre une taille en pixel, sinon laisser false.
```

* `$this->table1->allOk` => uniquement pour vérifier si l'upload s'est déroulé correctement.
```php
if($this->table1->allOk){
	// ok
}else{
	echo $this->table1->error; # affiche le message d'erreur
}
```

* `$this->table1->id` ($this->table->id) => Récupere le dernier id ajouté si un save() a été utilisé juste avant

* `if($_POST){}` => test si un post a été fait


### Envoyer des données de la DB à la vue

Dans les actions, quand les données sont récupérées, on les envoie à la vue grâce à la function render() avec un tableau en paramètre.
`$this->render(array())`

*Exemple:*

```php
$this->render(array(
	'message' => $listes_message,
));
```


### Message Flash

Créez un message Flash `$this->Session->setFlash('error', 'Mauvais identifiant');` ('error' ou 'ok' en premier paramètre)

envoyer le message à la vue:
```php
$this->render(array(
	"flash"	=>	$this->Session->flash(),
));
```

Recuperez le message dans la vue twig: `{{ flash|raw }}` , renvoie une div `<div class="flash flash-'type'">message</div>` (type = error ou ok).

### Redirection

`$this->redirectUrl('public:index.html.twig', $key);` 

-`public` est le nom du controller (sans Controller à la fin);
-`index.html.twig` est le nom de la vue;
-`$key` est la clé à envoyer dans l'url si on redirige vers une vue qui en a besoin (optionel);

### ROLE 

Dans la table qui contient les utilisateurs, ajoutez un champs `role` pour pouvoir gerer les droits d'accès.

Tout visiteur est automatiquement authentifier en tant que ROLE **'visiteur'**.

Pour limiter l'accès à certaines pages, utiliser: 	

```php
if(!$this->ROLE('admin')){ // exemple, la page est limitée aux membres qui ont comme ROLE admin.
  $this->redirectUrl('public:index.html.twig'); // ceux qui ne sont pas admin, ils seront redirigé sur l'index.
}
```

Par défaut, `$this->ROLE()` contient 'visiteur' et return true. 

### Connexion

Utilisez `$this->table->connexion($_POST);` dans une action au nom de votre choix pour un connexion.

`$this->user->connexion`, si tout est ok, return true

```php
$this->user->connexion($_POST); // envoi le formulaire
if($this->user->connexion){ // si la connexion s'est bien passée, return true
  $this->redirectUrl('public:choice.html.twig'); // on redirige
}else{
	$this->Session->setFlash('error', 'Mauvais identifiant'); // sinon on envoie un message flash
}
```

### Deconnexion

Utilisez le code ci bas dans une action au nom de votre choix pour une déconnexion.
```php
$this->user->deconnexion(); // modifier le ROLE actuel en 'visiteur'
$this->redirectUrl('public:index.html.twig'); // une fois fait, redirection sur l'index
```

## Views

Les vues sont dans le dossier `views`.

Les pages ont les mêmes noms que les actions (sans 'Action' à la fin) et sont placées dans le dossier des controllers et ont comme extension `.html.twig` (si Twig) `.tpl` (si Smarty) ou `.php` (si aucun template).

Si vous avez choisi le php classique, récupérez les données dans la vue avec `<?php echo $data['foo']; ?>` .

### Ressources
Les ressources Css/Js/Images/fonts, sont à placer dans `src / ressources`, chacun sont dans un dossier respectif.

![image](http://img4.hostingpics.net/pics/990630ressources.png)

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
