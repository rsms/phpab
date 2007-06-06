<?
/*
Copyright (c) 2005-2007, Rasmus Andersson

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
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