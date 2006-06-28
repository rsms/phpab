<?
/**
 * Inflector contains static methods to convert English words between
 * singular and plural, and phrases between the camel case form and
 * the lower case underscore form.
 *
 * @version    $Id$
 * @author     Rasmus Andersson
 * @author     John Peterson
 * @package    ab
 * @subpackage util
 */
class Inflector {

    # Rules for converting an English singular word to plural form
    private static $plural_rules =
        array(  '/(x|ch|ss|sh)$/' => '\1es',            # search, switch, fix, box, process, address
                '/series$/' => '\1series',
                '/([^aeiouy]|qu)ies$/' => '\1y',
                '/([^aeiouy]|qu)y$/' => '\1ies',        # query, ability, agency
                '/(?:([^f])fe|([lr])f)$/' => '\1\2ves', # half, safe, wife
                '/sis$/' => 'ses',                      # basis, diagnosis
                '/([ti])um$/' => '\1a',                 # datum, medium
                '/person$/' => 'people',                # person, salesperson
                '/man$/' => 'men',                      # man, woman, spokesman
                '/child$/' => 'children',               # child
                '/(.*)status$/' => '\1statuses',
                '/s$/' => 's',                          # no change (compatibility)
                '/$/' => 's'
        );

    # Rules for converting an English plural word to singular form
    private static $singular_rules =
        array(  '/(x|ch|ss)es$/' => '\1',
                '/movies$/' => 'movie',
                '/series$/' => 'series',
                '/([^aeiouy]|qu)ies$/' => '\1y',
                '/([lr])ves$/' => '\1f',
                '/([^f])ves$/' => '\1fe',
                '/(analy|ba|diagno|parenthe|progno|synop|the)ses$/' => '\1sis',
                '/([ti])a$/' => '\1um',
                '/people$/' => 'person',
                '/men$/' => 'man',
                '/(.*)statuses$/' => '\1status',
                '/children$/' => 'child',
                '/news$/' => 'news',
                '/s$/' => ''
        );

    /**
     *  Pluralize a word according to English rules
     *
     *  Convert a lower-case singular word to plural form.
     *  @param  string  Word to be pluralized
     *  @return string  Plural of $word
     */
    function pluralize($word) {
        $original = $word;
        foreach(self::$plural_rules as $rule => $replacement) {
            $word = preg_replace($rule,$replacement,$word);
            if($original != $word) break;
        }
        return $word;
    }

    /**
     *  Singularize a word according to English rules 
     *
     *  @param  string  Word to be singularized
     *  @return string  Singular of $word
     */
    function singularize($word) {
        $original = $word;
        foreach(self::$singular_rules as $rule => $replacement) {
            $word = preg_replace($rule,$replacement,$word);
            if($original != $word) break;
        }
        return $word;
    }

    /**
     *  Capitalize a word making it all lower case with first letter uppercase 
     *
     *  @param  string  Word to be capitalized
     *  @return string  Capitalized $word
     */
    function capitalize($word) {
        return ucfirst(strtolower($word));     
    }

    /**
     *  Convert a phrase from the lower case and underscored form
     *  to the camel case form
     *
     *  @param  string  Phrase to convert
     *  @return string  Camel case form of the phrase
     */
    function camelize($lower_case_and_underscored_word) {
    	if(strpos($lower_case_and_underscored_word, '_') === false)
			return ucfirst($lower_case_and_underscored_word);
		else
			return str_replace(' ','',ucwords(str_replace('_',' ',$lower_case_and_underscored_word)));
    }

    /**
     *  Convert a phrase from the camel case form to the lower case
     *  and underscored form.
     *
     *  @param  string  Phrase to convert
     *  @return string  Lower case and underscored form of the phrase
     */
    function underscore($camel_cased_word) {
        $camel_cased_word = preg_replace('/([A-Z]+)([A-Z])/','\1_\2',$camel_cased_word);
        return strtolower(preg_replace('/([a-z])([A-Z])/','\1_\2',$camel_cased_word));
    }

    /**
     *  Generate a more human version of a lower case underscored word
     *
     *  @param  string  A word or phrase in lower_case_underscore form
     *  @return string  The input value with underscores replaced by blanks and 
     *                  the first letter of each word capitalized
     */
    function humanize($lower_case_and_underscored_word) {
        return ucwords(str_replace("_"," ",$lower_case_and_underscored_word));
    }

    /**
     *  Convert a class name to the corresponding table name
     *
     *  The class name is a singular word or phrase in CamelCase.
     *  By convention it corresponds to a table whose name is a plural
     *  word or phrase in lower case underscore form.
     * 
     *  @param  string  Name of Model class
     *  @return string  Pluralized lower_case_underscore form of name
     */
    function tableize($class_name) {
        return self::pluralize(self::underscore($class_name));
    }

    /**
     *  Convert a table name to the corresponding class name
     *
     *  @param  string  Name of table in the database
     *  @return string  Singular CamelCase form of $table_name
     */
    function classify($table_name) {
        return self::camelize(self::singularize($table_name));
    }

    /**
     *  Get foreign key column corresponding to a table name
     *
     *  @param  string  Name of table referenced by foreign key
     *  @return string  Column name of the foreign key column
     */
    function foreignKey($class_name) {
        return self::underscore($class_name) . "_id";
    }
    
}


?>