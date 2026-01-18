import sharp from 'sharp';
import { writeFileSync } from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const svgPath = join(__dirname, 'public', 'favicon.svg');
const publicDir = join(__dirname, 'public');

// Read the SVG
const svgBuffer = await sharp(svgPath).toBuffer();

// Generate different sizes
const sizes = [
  { size: 16, name: 'favicon-16x16.png' },
  { size: 32, name: 'favicon-32x32.png' },
  { size: 180, name: 'apple-touch-icon.png' },
];

console.log('Generating favicon files...');

// Generate PNG files
for (const { size, name } of sizes) {
  await sharp(svgBuffer)
    .resize(size, size, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
    .png()
    .toFile(join(publicDir, name));
  console.log(`✓ Created ${name}`);
}

// Generate ICO file (16x16 and 32x32)
const ico16 = await sharp(svgBuffer)
  .resize(16, 16, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
  .png()
  .toBuffer();

const ico32 = await sharp(svgBuffer)
  .resize(32, 32, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
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

console.log('\n✓ All favicon files generated successfully!');
