// Total number of seconds in a full day (24 * 60 * 60)
export const SECS_DAY = 86400;

/**
 * Converts a time string (HH:MM:SS) to total seconds.
 * @param {string} hms - Time in the format "hh:mm:ss"
 * @returns {number} Total seconds
 */
export function hmsToSec(hms) {
    const [h, m, s] = hms.split(':').map(Number);
    return h * 3600 + m * 60 + s;
}

/**
 * Converts a number of seconds into a human-readable string like "2u 15m".
 * @param {number} sec - Number of seconds
 * @returns {string} Time in hours and minutes
 */
export function secToMinTxt(sec) {
    const hours = Math.floor(sec / 3600);
    const minutes = Math.floor((sec % 3600) / 60);
    return hours ? `${hours}u ${minutes}m` : `${minutes}m`;
}

/**
 * Compares two sets for exact equality (same elements, same size).
 * @param {Set} setA
 * @param {Set} setB
 * @returns {boolean} True if sets contain the same items
 */
export function setsAreEqual(setA, setB) {
    return setA.size === setB.size && [...setA].every(a => setB.has(a));
}
