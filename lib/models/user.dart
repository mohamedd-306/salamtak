class User {
  final String? id; // Firebase UID (String)
  final String nationalId;
  final String phoneNumber;
  final String name;
  final String userType; // 'user', 'moderator', 'product_manager'
  final String? email;
  final String? address;

  User({
    this.id,
    required this.nationalId,
    required this.phoneNumber,
    required this.name,
    required this.userType,
    this.email,
    this.address,
  });

  // Helper methods to check user roles
  bool get isUser => userType == 'user';
  bool get isModerator => userType == 'moderator';
  bool get isProductManager => userType == 'product_manager';
  bool get isAdmin => isModerator || isProductManager; // Any admin type

  Map<String, dynamic> toMap() => {
    'id': id,
    'nationalId': nationalId,
    'phoneNumber': phoneNumber,
    'name': name,
    'userType': userType,
    if (email != null) 'email': email,
    if (address != null) 'address': address,
  };

  factory User.fromMap(Map<String, dynamic> map) => User(
    id: map['id']?.toString(),
    nationalId: map['nationalId'] ?? '',
    phoneNumber: map['phoneNumber'] ?? map['phone'] ?? '',
    name: map['name'] ?? '',
    userType: map['userType'] ?? 'user',
    email: map['email'],
    address: map['address'],
  );
}
