/**
 * Convertit un nombre décimal d'heures en format HH:mm:ss
 * @param {number|string} decimalHours - Nombre d'heures décimales (ex: 8.5)
 * @returns {string} Format HH:mm:ss (ex: "08:30:00")
 */
export const formatHours = (decimalHours) => {
    if (!decimalHours && decimalHours !== 0) return '00:00:00';
    
    const hours = parseFloat(decimalHours);
    if (isNaN(hours)) return '00:00:00';
    
    const wholeHours = Math.floor(hours);
    const minutes = Math.round((hours - wholeHours) * 60);
    const seconds = 0;
    
    return `${String(wholeHours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
};

/**
 * Convertit un nombre décimal d'heures en format HH:mm
 * @param {number|string} decimalHours - Nombre d'heures décimales (ex: 8.5)
 * @returns {string} Format HH:mm (ex: "08:30")
 */
export const formatHoursShort = (decimalHours) => {
    if (!decimalHours && decimalHours !== 0) return '00:00';
    
    const hours = parseFloat(decimalHours);
    if (isNaN(hours)) return '00:00';
    
    const wholeHours = Math.floor(hours);
    const minutes = Math.round((hours - wholeHours) * 60);
    
    return `${String(wholeHours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
};

/**
 * Convertit un nombre décimal d'heures en format HH:mm:ss avec signe (+/-)
 * Utilisé pour les écarts/déviations
 * @param {number|string} decimalHours - Nombre d'heures décimales (ex: 2.5 ou -1.75)
 * @returns {string} Format +HH:mm:ss ou -HH:mm:ss (ex: "+02:30:00", "-01:45:00")
 */
export const formatHoursDeviation = (decimalHours) => {
    if (!decimalHours && decimalHours !== 0) return '+00:00:00';
    
    const hours = parseFloat(decimalHours);
    if (isNaN(hours)) return '+00:00:00';
    
    const isNegative = hours < 0;
    const absoluteHours = Math.abs(hours);
    
    const wholeHours = Math.floor(absoluteHours);
    const minutes = Math.round((absoluteHours - wholeHours) * 60);
    const seconds = 0;
    
    const sign = isNegative ? '-' : '+';
    return `${sign}${String(wholeHours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
};
