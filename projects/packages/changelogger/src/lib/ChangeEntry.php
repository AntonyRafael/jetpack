<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Class representing a change entry.
 *
 * @package automattic/jetpack-changelogger
 */

// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

namespace Automattic\Jetpack\Changelog;

use DateTime;
use InvalidArgumentException;

/**
 * Class representing a change entry.
 */
class ChangeEntry {

	/**
	 * Entry significance.
	 *
	 * @var string|null
	 */
	protected $significance = 'unknown';

	/**
	 * Entry timestamp.
	 *
	 * @var DateTime
	 */
	protected $timestamp;

	/**
	 * Subheading the entry is under.
	 *
	 * @var string
	 */
	protected $subheading = '';

	/**
	 * Author of the entry.
	 *
	 * @var string
	 */
	protected $author = '';

	/**
	 * Content of the entry.
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * Constructor.
	 *
	 * @param array $data Data for entry fields. Keys correspond to the setters, e.g. key 'content' calls `setContent()`.
	 * @throws InvalidArgumentException If an argument is invalid.
	 */
	public function __construct( array $data = array() ) {
		$data = $data + array( 'timestamp' => 'now' );
		foreach ( $data as $k => $v ) {
			$func = array( $this, 'set' . ucfirst( $k ) );
			if ( is_callable( $func ) ) {
				$func( $v );
			} else {
				throw new InvalidArgumentException( __METHOD__ . ": Unrecognized data item \"$k\"." );
			}
		}
	}

	/**
	 * Compare two ChangeEntry objects.
	 *
	 * @param ChangeEntry $a First ChangeEntry.
	 * @param ChangeEntry $b Second ChangeEntry.
	 * @param array       $config Comparison configuration. Keys are:
	 *        - ordering: (string[]) Order in which to consider the fields. Field
	 *          names correspond to getters, e.g. 'significance' =>
	 *          `getSignificance()`. Default ordering is subheading, content.
	 *        - knownSubheadings: (string[]) Change entries with these headings will
	 *          compare, in this order, after those with no subheading and before any
	 *          other subheadings.
	 * @return int <0 if $a should come before $b, >0 if $b should come before $a, or 0 otherwise.
	 * @throws InvalidArgumentException If an argument is invalid.
	 */
	public static function compare( ChangeEntry $a, ChangeEntry $b, array $config = array() ) {
		$config += array(
			'ordering'         => array( 'subheading', 'content' ),
			'knownSubheadings' => array(),
		);

		foreach ( $config['ordering'] as $field ) {
			// First, check for a custom comparison function.
			$func = array( static::class, 'compare' . ucfirst( $field ) );
			if ( is_callable( $func ) ) {
				$ret = $func( $a, $b, $config );
			} else {
				// Otherwise, just use `strnatcasecmp()`.
				$func = 'get' . ucfirst( $field );
				if ( ! is_callable( array( $a, $func ) ) || ! is_callable( array( $b, $func ) ) ) {
					throw new InvalidArgumentException( __METHOD__ . ': Invalid field in ordering' );
				}
				$aa  = call_user_func( array( $a, $func ) );
				$bb  = call_user_func( array( $b, $func ) );
				$ret = strnatcasecmp( $aa, $bb );
			}
			if ( 0 !== $ret ) {
				return $ret;
			}
		}

		return 0;
	}

	/**
	 * Get the significance.
	 *
	 * @return string|null
	 */
	public function getSignificance() {
		return $this->significance;
	}

	/**
	 * Set the significance.
	 *
	 * @param string|null $significance 'patch', 'minor', or 'major'.
	 * @returns $this
	 * @throws InvalidArgumentException If an argument is invalid.
	 */
	public function setSignificance( $significance ) {
		if ( ! in_array( $significance, array( null, 'patch', 'minor', 'major' ), true ) ) {
			throw new InvalidArgumentException( __METHOD__ . ": Significance must be 'patch', 'minor', or 'major' (or null)" );
		}
		$this->significance = $significance;
		return $this;
	}

	/**
	 * Compare significance values.
	 *
	 * @param ChangeEntry $a First entry.
	 * @param ChangeEntry $b Second entry.
	 * @param array       $config Unused.
	 * @return int
	 */
	protected function compareSignificance( ChangeEntry $a, ChangeEntry $b, array $config ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		static $values = array( 'major', 'minor', 'patch', null );
		$aa            = array_search( $a->getSignificance(), $values, true );
		$bb            = array_search( $b->getSignificance(), $values, true );
		return $aa - $bb;
	}

	/**
	 * Get the timestamp.
	 *
	 * @return DateTime
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * Set the timestamp.
	 *
	 * @param DateTime|string $timestamp Timestamp to set.
	 * @returns $this
	 * @throws InvalidArgumentException If an argument is invalid.
	 */
	public function setTimestamp( $timestamp ) {
		if ( ! $timestamp instanceof DateTime ) {
			try {
				$timestamp = new DateTime( $timestamp );
			} catch ( \Exception $ex ) {
				throw new InvalidArgumentException( __METHOD__ . ': Invalid timestamp', 0, $ex );
			}
		}
		$this->timestamp = $timestamp;
		return $this;
	}

	/**
	 * Compare timestamps.
	 *
	 * @param ChangeEntry $a First entry.
	 * @param ChangeEntry $b Second entry.
	 * @param array       $config Unused.
	 * @return int
	 */
	protected function compareTimestamp( ChangeEntry $a, ChangeEntry $b, array $config ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$aa = $a->getTimestamp();
		$bb = $b->getTimestamp();
		return $aa < $bb ? -1 : ( $aa > $bb ? 1 : 0 );
	}

	/**
	 * Get the subheading.
	 *
	 * @return string
	 */
	public function getSubheading() {
		return $this->subheading;
	}

	/**
	 * Set the subheading.
	 *
	 * @param string $subheading Subheading to set.
	 */
	public function setSubheading( $subheading ) {
		$this->subheading = (string) $subheading;
	}

	/**
	 * Get the author.
	 *
	 * @return string
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * Set the author.
	 *
	 * @param string $author Author to set.
	 */
	public function setAuthor( $author ) {
		$this->author = (string) $author;
	}

	/**
	 * Get the content.
	 *
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Set the content.
	 *
	 * @param string $content Content to set.
	 */
	public function setContent( $content ) {
		$this->content = (string) $content;
	}

}