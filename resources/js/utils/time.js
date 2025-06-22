export const SECS_DAY = 86400;

export function hmsToSec(hms) {
    const [h, m, s] = hms.split(':').map(Number);
    return h * 3600 + m * 60 + s;
}

export function secToMinTxt(sec) {
    const hours = Math.floor(sec / 3600);
    const minutes = Math.floor((sec % 3600) / 60);
    return hours ? `${hours}u ${minutes}m` : `${minutes}m`;
}

export function setsAreEqual(setA, setB) {
    return setA.size === setB.size && [...setA].every(a => setB.has(a));
}
