# Kinto'un [Framework]
v 1.0.3

Nécessite PHP 5.4 ou +

[Utilise le template Twig/Smarty ou Php classique] 


## Installation

Extraire le dossier `Kinto-Un` à la racine de votre site. (/!\ N'oubliez pas le `.htaccess` )
Configurez le dossier `config.yml` se trouvant à `core/config.yml`, spécifiez correctement les champs demandés.

### Composer

**/!\ Faire un update de Composer pour installer les templates où le framework ne fonctionnera pas /!\** 

plus d'info sur Composer => `https://getcomposer.org/doc/00-intro.md`

Par défault, le template est Twig.
Pour utiliser Smarty, le rajouter dans `composer.json`.

## Fichier config

Le fichier de config `config.yml` se trouvant à `core/config.yml` vous permettra de vous connecter à la DB, de choisir le template (none/php, Twig ou Smarty) et d'indiquer le projet principal.


## Project

Les projets sont des dossiers contenant un enssemble de code permettant par la suite d'être réutilisé plus facilement.

Les projets sont à placer dans le dossier `src/project`.
Un projet se compose:
![image](http://img11.hostingpics.net/pics/969961folder.png)

Le projet principal (qui sera lancé en premier en visitant le site) doit être indiqué dans le fichier `config.yml` se trouvant dans le dossier `core` sous la clé `default_project`.


## Controller

Seulement 2 Controllers

* PublicController
* AdminController

Dans les pages Controller (publicController et adminController) créez des actions, le nom des actions auront le même nom que les pages dans les vues **(sans 'Action' à la fin)**.

![image](http://img15.hostingpics.net/pics/932424action.png)


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

* `$this->table1->findAll(array())` => Dans le tableau, mettre des conditions => "where", "fields", "limit", "order"

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

* `$this->table1->allOk()` => uniquement pour vérifier si l'upload s'est déroulé correctement.
```php
if($this->table1->allOk()){
	// ok
}else{
	echo $this->table1->getError(); # affiche le message d'erreur
}
```

* `$this->table1->lastId()` ($this->table->lastId()) => Récupere le dernier id ajouté si un save() a été utilisé juste avant

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

Créez un message Flash `$this->Session->setFlash('error', 'Mauvais identifiant');` (le type du message en premier paramètre)

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

### Envoi de mail

`$this->SendMail->send($to,$fromName,$fromMail,$subject,$message);`

(Les messages peuvent contenir de l'html)

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

`$this->user->testConnect()`, si tout est ok, return true

```php
$this->user->connexion($_POST); // envoi le formulaire
if($this->user->testConnect()){ // si la connexion s'est bien passée, return true
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

## Routing

Le fichier de config de routing se trouve à `src/ressources/config/routing.yml`.

```yml
view_article:
    pattern:  /{_lang}/article/{slug}_{id}
    controller: home:public:view
```

* `view_article` => Est le nom de la route, qui sera utile pour la fonction path().
* `pattern` => Est le lien subjectif désiré avec ces paramètres.
* `controller` => Est le chemin vers le project/controller/action. 

{_lang} peut être absent dans le l'url définitif.

### lien

Dans les vues, une fonction twig permet de créer des liens dynamiquement.
`<a href="{{path("view_article",{"slug": "mon-beau-slug", "id": "9"})}}">cliquez moi</a>`
le parametre {_lang} n'est pas obligatoire, si il est vide, il ajoutera automatiquement la langue de la session.

retournera:
`<a href="_ROOT_/_session-langue_/article/mon-beau-slug_9">cliquez moi</a>`

## Multilangage

Kinto'Un permet de traduire son site très facilement.

Pour se faire, créez un fichier `.yml` dans `src/ressources/translate/` et nommez le à la langue que vous souhaitez traduire (2 lettres).
Si mon site est en Français et je veux le traduire en anglais, je nomerai le fichier `en.yml`.

Dans ce fichier, écrivez d'un coté la sentence cible (la langue de votre site) ensuite vous mettez deux points `:` et la sentence à la langue que vous voulez traduire.

```yml
Bonjour: Hello
Comment allez vous: How are you
"Numéro de téléphone:": Phone number: 
```

Dans la vue, pour traduire vos sentences, les manières varient selon le template utilisé:

* Twig => `{{"Bonjour"|trans}}`
* Smarty => `{"Bonjour"|trans}`
* Php => `<?= echo trans("Bonjour"); ?>`


## Créez vos filtres/fonctions

Créez votre fichier php dans le dossier `libs/template/extensions/`.
Danse ce fichier, créez votre fonction qui servira de filtre ou de fonction.
Votre fichier doit avoir le même nom que votre fonction et se terminer par _function ou _filter et ce nom servira comme filtre/fonction pour tout les templates.

## Personnalisez la page 404 et les autres pages d'erreur

Un template de base est situé à `core/errors/error.html.twig` et les ressources sont dans le dossier `ressources`.
Le controller `errorController.php` se trouvant à `core/errors/errorController.php` vous permet de rendre votre page dynamique.

## REST

Kinto'Un permet l'utilisation de REST.
Dans le controller, testez la requète pour savoir de quel type elle est.

* POST => Request::POST();
* GET => Request::GET();
* PUT => Request::PUT();
* DELETE => Request::DELETE();

Toute les requètes doit être testé avec une condition et return true si la requète utilisé est celle de la condition. Une condition n'est pas obligatoire uniquement pour GET et peut envoyer directement un tableau comme option.
Pour retourner un JSON utiliser $this->renderJson($data);

Pour récupérer la valeur envoyer par une requète:
* POST => $_POST
* PUT => $this->_PUT ou Request::$_PUT
* DELETE => le paramètre de l'action
* GET => une requète de la class Model ($this->table->findAll(); / $this->table->findById($id))



```php
# lien: monsite.com/article/

public function indexAction(){
	$data = $this->table->findAll();

	Request::GET($data); # une condition n'est pas obligatoire pour GET

	if(Request::POST()){
		$this->livreor->save($_POST);
	}else{ #sinon retourne une page html
		$this->render(array(
			"articles"	=>	$data,
		));
	}
}
```

```php
# lien: monsite.com/article/44

public function viewAction($id){
	$data = $this->table->findById($id);

	Request::GET($data); # une condition n'est pas obligatoire pour GET

	if(Request::PUT()){
		$this->table->save(Request::$_PUT);
	}elseif (Request::DELETE()){
		$this->table->delete($id);
	}else{ # Sinon retourne une page html
		$this->render(array(
			"article"	=>	$data,
		));				
	}
}
```