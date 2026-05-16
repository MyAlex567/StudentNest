function viewDocument(filename, type) {
    if (type === 'jpg' || type === 'jpeg') {
        window.open('./' + filename, '_blank');
    } else {
        window.location.href = `viewer.php?file=${encodeURIComponent(filename)}&type=${type}`;
    }
}