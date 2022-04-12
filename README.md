# Laravel ID Customizer
**_A laravel package to customize IDs formats._**

In many laravel applications, using standar behaviors for generating IDs is not good idea for some Models. That's why many devs like to generate application IDs as prefixed auto incremental and someone like to generate their unique ID in a custom format.  
This package will help you to generate a custom primary key or any field in your table with a custom ID.

### Installation :
`composer require hexify/laravel-id-customizer`
### Usage :
You can use it in your controller or inside your model by using trait.
* ##### Using it in controller :
First import ID Customizer in controller `use Hexify\LaraIdCustomizer\IdCustomizer;`.  
Then in your method :
```
public function store(Request $request){
  $config = [
    'model' => Student::class,
    'column' => 'uid',
    'length' => 10,
    'prefix' => date('ym')
  ];

  $uid = IdCustomizer::generate($config);

  $student = new Student();
  $student->uid = $uid; // a fillable field.
  $student->name = $request->input('name');
  ...
  $student->save();
}
```
* ##### Using it in model directly :
In your model :  
-Implement the interface `Hexify\LaraIdCustomizer\IdFactory;`  
-Add the trait `Hexify\LaraIdCustomizer\Traits\HasIdFactory;`.
```
...
use Hexify\LaraIdCustomizer\IdFactory;
use Hexify\LaraIdCustomizer\Traits\HasIdFactory;

class Student extends Model implements IdFactory {
  ...
  use HasIdFactory;
  ...
}
```

Add this array below in your model to customize the `uid` field value.
```
/**
* The configs that used for generating custom ID.
*
* @var array
*/
private static $idFactoryConfig = 
  \\******************* INCREMENTAL *******************
 [
    'factory_method' => self::INCREMENTAL, \\ Optional. Default is self::INCREMENTAL.
    'column' => 'uid', \\ Default is 'id'.
    'length' => 10, \\ Default is 10.
    'prefix' => 'STD-', \\ Or for example 'USR-'. Default is ''.
    'reset_on_prefix_change' => false, \\ Default is true.
 ];
  \\********************* RANDOM **********************
 [
    'factory_method' => self::RANDOM,
    'column' => 'uid',
    'length' => 10,
    'prefix' => '',
    'set' => self::NUMERIC, \\ Or  self::ALPHA | self::ALPHA_NUMERIC. Default is self::ALPHA_NUMERIC.
    'extra' => 'abcdef', \\ Default is ''.
 ];
```
Or `private static $idFactoryConfig = [];` for default configuration.
### Parameters explanation :
There is two methods for generating custom IDs 'INCREMENTAL' and 'RANDOM'.
* Incremental like :  
  `STD-0001` `STD-0002` `USR-0003` ..., while reset on prefix change is false

  `STD-0001` `STD-0002` `USR-0001` ..., while reset on prefix change is true  
* Random like :  
  `STD-4a67` `STD-cc32` `STD-19B5` ..., using 'ALPHA_NUMERIC' characters

  `STD-9458` `STD-3498` `STD-7453` ..., using 'NUMERIC' characters
  
  `STD-agKe` `STD-BvtM` `STD-crrQ` ..., using 'ALPHA' characters

Each method must have `model`, `column`, `length`, `prefix`

`model` : your model class, Ex : `Student::class`.  
`column`: Optional, Default is Model KeyName.  
`length`: ID length. Optional, Default is 10.  
`prefix`: Define your prefix. It can be a year, month or any custom letters. Optional.  

`N.B, if the 'column' type is (int, integer, bigint or numeric) the 'prefix' must be numeric as well.`

* With the addition of the following option in 'INCREMENTAL' method  
`reset_on_prefix_change`:  Optional, default true. If you want reset `column` value to 1 on prefix change then set it true.  

* Or with the addition of the following options in 'RANDOM' method  
`set`: Optional, default 'ALPHA_NUMERIC'. is a set of characters used for generating random value.  
`extra`: Optional, add extra characters to `set` option.
### Examples :
#### => Incremental
* Example 01: generating 'STD-0001' ID, prefix is a non numeric value so your column should be varchar type.  
```
IdCustomizer::generate(
  [
    'model' => Student::class,
    'length' => 8,
    'prefix' => 'STD-'
  ]
);
```
* Example 02: 'YYMM00001' ID.  
```
IdCustomizer::generate(
  [
    'model' => Invoice::class,
    'length' => 9,
    'prefix' => date('ym')
  ]
);
Out put => 220300001
```
* Example 03: Determine the desired column.  
```
IdCustomizer::generate(
  [
    'model' => User::class,
    'column' => 'uid',
    'length' => 10,
    'prefix' => date('ym')
  ]
);
Out put => 2203000001
```
* Example 04: If you don't want to reset the identifier when the prefix change.  
```
IdCustomizer::generate(
  [
    'model' => User::class,
    'column' => 'uid',
    'length' => 10,
    'prefix' => date('ym'),
    'reset_on_prefix_change' => false
  ]
);
Out put => 2204000002
```
#### => Random
* Example 01: 'IMG-k9C42a' ID.  
```
IdCustomizer::generate(
  [
    'factory_method' => IdCustomizer::RANDOM,
    'model' => Media::class,
    'length' => 10,
    'prefix' => 'IMG-',
    'set' => IdCustomizer::ALPHA_NUMERIC
  ]
);
Out put => IMG-k9C42a
```
* Example 02: 'USR-YYMM****' ID.  
```
IdCustomizer::generate(
  [
    'factory_method' => IdCustomizer::RANDOM,
    'model' => User::class,
    'length' => 13,
    'prefix' => 'USR-'.date('ym'),
    'set' => IdCustomizer::NUMERIC
  ]
);
Out put => USR-22035673
```
* Example 03: Define a custom `set`.  
```
IdCustomizer::generate(
  [
    'factory_method' => IdCustomizer::RANDOM,
    'model' => User::class,
    'column' => 'uid',
    'length' => 10,
    'prefix' => 'U-'
    'set' => 'ABCDabcd1234'
  ]
);
Out put => U-Ac32Dbb4
```
* Example 04: Add extra characters to `set`.  
```
IdCustomizer::generate(
  [
    'factory_method' => IdCustomizer::RANDOM,
    'model' => User::class,
    'column' => 'uid',
    'length' => 10,
    'prefix' => 'S-'
    'set' => IdCustomizer::NUMERIC,
    'extra' => 'abcdef'
  ]
);
Out put => S-f059ba01
```