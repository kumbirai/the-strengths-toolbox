import sharp from 'sharp';
import { writeFileSync } from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const logoPath = join(__dirname, 'public', 'images', 'logo.png');
const publicDir = join(__dirname, 'public');

// Read the logo
const logoBuffer = await sharp(logoPath).toBuffer();

// Generate different sizes
const sizes = [
  { size: 16, name: 'favicon-16x16.png' },
  { size: 32, name: 'favicon-32x32.png' },
  { size: 180, name: 'apple-touch-icon.png' },
];

console.log('Generating favicon files from logo.png...');

// Generate PNG files
for (const { size, name } of sizes) {
  await sharp(logoBuffer)
    .resize(size, size, { fit: 'contain', background: { r: 255, g: 255, b: 255, alpha: 1 } })
    .png()
    .toFile(join(publicDir, name));
  console.log(`✓ Created ${name}`);
}

// Generate SVG favicon (scalable version)
// Convert logo to base64 data URI for embedding
const logoBase64 = await sharp(logoPath)
  .resize(512, 512, { fit: 'contain', background: { r: 255, g: 255, b: 255, alpha: 1 } })
  .png()
  .toBuffer()
  .then(buffer => buffer.toString('base64'));

const svgContent = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <image href="data:image/png;base64,${logoBase64}" width="512" height="512" preserveAspectRatio="xMidYMid meet"/>
</svg>`;
writeFileSync(join(publicDir, 'favicon.svg'), svgContent);
console.log('✓ Created favicon.svg');

// Generate ICO file (16x16 and 32x32)
const ico16 = await sharp(logoBuffer)
  .resize(16, 16, { fit: 'contain', background: { r: 255, g: 255, b: 255, alpha: 1 } })
  .png()
  .toBuffer();

const ico32 = await sharp(logoBuffer)
  .resize(32, 32, { fit: 'contain', background: { r: 255, g: 255, b: 255, alpha: 1 } })
  .png()
  .toBuffer();

// Create ICO file structure
// ICO format: header + directory entries + image data
const createICO = (images) => {
  const header = Buffer.alloc(6);
  header.writeUInt16LE(0, 0); // Reserved (must be 0)
  header.writeUInt16LE(1, 2); // Type (1 = ICO)
  header.writeUInt16LE(images.length, 4); // Number of images

  let offset = 6 + (images.length * 16); // Header + directory
  const directory = [];
  const imageData = [];

  images.forEach((img, index) => {
    const dir = Buffer.alloc(16);
    dir.writeUInt8(img.width === 256 ? 0 : img.width, 0); // Width
    dir.writeUInt8(img.height === 256 ? 0 : img.height, 1); // Height
    dir.writeUInt8(0, 2); // Color palette (0 = no palette)
    dir.writeUInt8(0, 3); // Reserved
    dir.writeUInt16LE(1, 4); // Color planes
    dir.writeUInt16LE(32, 6); // Bits per pixel
    dir.writeUInt32LE(img.data.length, 8); // Image data size
    dir.writeUInt32LE(offset, 12); // Offset to image data

    directory.push(dir);
    imageData.push(img.data);
    offset += img.data.length;
  });

  return Buffer.concat([header, ...directory, ...imageData]);
};

const icoFile = createICO([
  { width: 16, height: 16, data: ico16 },
  { width: 32, height: 32, data: ico32 },
]);

writeFileSync(join(publicDir, 'favicon.ico'), icoFile);
console.log('✓ Created favicon.ico');

console.log('\n✓ All favicon files generated successfully from logo.png!');
