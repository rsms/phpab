<?
/**
 * @version    $Id$
 * @author     Rasmus Andersson
 * @package    ab
 * @subpackage io
 */
class IO {
	
	/** @var resource */
	private static $stdErrFD = null;
	
	/**
	 * Print something to stderr
	 *
	 * @param  mixed
	 * @return void
	 */
	public static function writeError( $str )
	{
		if(!self::$stdErrFD)
			self::$stdErrFD = fopen('php://stderr', 'w');
		fwrite(self::$stdErrFD, $str);
	}
    
    /**
     * Unserialize file
	 *
	 * Convenience method for unarchiving a serialized file and return it's data.
     * 
     * @param  string
     * @return mixed   Unserialized structure/value
     * @throws IOException
	 * @see    serialize()
     */
    public static function unserializeFile( $file )
	{
        if(($dat = @file_get_contents($file)) === false)
        	throw new IOException('Failed to read file "' . $file . '"');
		
        if(($d = @unserialize($dat)) === false)
        	if($dat !== serialize(false))
				throw new IOException('Failed to unserialize file "' . $file . '"');
        
        return $d;
    }
	
	/**
	 * Serialize data and return or write the binary data to a file.
	 * 
	 * @param  mixed
	 * @param  string  If null, the serialized data is returned, else it's written to file and void is returned.
	 * @return string  Binary data (or void if $file is specified)
	 * @throws IOException
	 * @see    unserializeFile()
	 */
	public static function serialize( $data, $file = null )
	{
		if(($data = @serialize($data)) === false)
			throw new IOException('Failed to serialize data');
		
		if($file !== null) {
			if(@file_put_contents($file, $data) === false)
				throw new IOException('Failed to write serialized data to file "' . $file . '"');
		}
		else return $data;
	}
}
?>