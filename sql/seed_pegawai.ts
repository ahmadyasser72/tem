import { fakerID_ID as faker } from '@faker-js/faker';

// Wilayah Martapura
const kecamatanMartapura = [
  "Martapura Kota",
  "Martapura Barat",
  "Martapura Timur",
  "Gambut",
  "Kertak Hanyar",
  "Astambul",
  "Karang Intan"
];

// Jalan populer di Martapura
const jalanMartapura = [
  "Jl. Menteri Empat",
  "Jl. A. Yani Km 40",
  "Jl. Sekumpul",
  "Jl. Pendidikan",
  "Jl. Karang Intan",
  "Jl. Veteran",
  "Jl. Martapura Lama",
  "Jl. Astambul Raya"
];

// Distribusi tidak adil -> lebih banyak pegawai di unit kecil dan jabatan tertentu
function randomWeighted(list, weights) {
  const sum = weights.reduce((a, b) => a + b, 0);
  let rand = Math.random() * sum;
  for (let i = 0; i < list.length; i++) {
    if (rand < weights[i]) return list[i];
    rand -= weights[i];
  }
  return list[0];
}

function randomDate(start, end) {
  return faker.date.between({ from: start, to: end }).toISOString().split("T")[0];
}

function generateNIP() {
  return faker.string.numeric(18);
}

const totalData = 200;

// Distribusi pangkat *tidak adil* (lebih banyak golongan II dan III)
const pangkatWeighted = [
  1, 2, 3, 4,  // Golongan I (jarang)
  15, 15, 20, 20,  // Golongan II (banyak)
  25, 25, 30, 30,  // Golongan III (terbanyak)
  5, 4, 3, 2, 1    // Golongan IV (sedikit)
];

// Distribusi jabatan *tidak adil* (lebih banyak pelaksana dan regu)
const jabatanWeighted = Array.from({ length: 20 }, (_, i) =>
  i < 10 ? 20 : 5
);

// Distribusi unit *tidak adil* (Martapura Kota & Martapura Barat lebih banyak)
const unitList = Array.from({ length: 20 }, (_, i) => i + 1);
const unitWeighted = [
  30, 25, 20, 20, 10, 10, 10, 5, 5, 5,
  3, 3, 3, 3, 2, 2, 1, 1, 1, 1
];

// Distribusi jenis kelamin (lebih banyak L)
const genderWeighted = ["L", "P"];
const genderRate = [70, 30];

let sqlValues = [];

for (let i = 0; i < totalData; i++) {
  const gender = randomWeighted(genderWeighted, genderRate);
  const tempat = faker.helpers.arrayElement(kecamatanMartapura);

  const nama = faker.person.fullName({
    sex: gender === "L" ? "male" : "female"
  });

  const pangkat = randomWeighted(
    Array.from({ length: 17 }, (_, i) => i + 1),
    pangkatWeighted
  );

  const jabatan = randomWeighted(
    Array.from({ length: 16 }, (_, i) => i + 1),
    jabatanWeighted
  );

  const unit = randomWeighted(unitList, unitWeighted);

  const sql = `(
    '${generateNIP()}',
    '${nama}',
    '${tempat}',
    '${randomDate("1970-01-01", "2000-12-31")}',
    '${gender}',
    '${faker.helpers.arrayElement(["Islam", "Kristen", "Katolik", "Hindu", "Buddha", "Konghucu"])}',
    '${faker.helpers.arrayElement(["Belum Kawin", "Kawin", "Cerai Hidup", "Cerai Mati"])}',
    '${faker.helpers.arrayElement(jalanMartapura)} No. ${faker.number.int({ min: 1, max: 200 })}, ${tempat}, Martapura',
    '${faker.phone.number("08##########")}',
    '${faker.internet.email().toLowerCase()}',
    ${pangkat},
    ${jabatan},
    ${unit},
    '${randomDate("2010-01-01", "2024-12-31")}',
    '${faker.helpers.arrayElement(["PNS", "Honorer", "Kontrak"])}',
    NULL,
    '${faker.helpers.arrayElement(["A", "B", "AB", "O"])}',
    '${faker.lorem.sentence()}',
    TRUE
  )`;

  sqlValues.push(sql);
}

console.log(`
INSERT INTO pegawai (
  nip, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin,
  agama, status_perkawinan, alamat_rumah, telepon, email,
  id_pangkat, id_jabatan, id_unit,
  tanggal_masuk, status_pegawai,
  foto_profil, darah, keterangan, is_active
) VALUES
${sqlValues.join(",\n")};
`);
